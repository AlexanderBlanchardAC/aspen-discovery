import React from 'react';
import { StyleSheet } from 'react-native';
import { View, Box, Button, Center, VStack } from 'native-base';
import { BarCodeScanner } from 'expo-barcode-scanner';
import { loadingSpinner } from './loadingSpinner';
import { loadError } from './loadError';
import { navigateStack } from '../helpers/RootNavigator';
import BarcodeMask from 'react-native-barcode-mask';
import { getTermFromDictionary } from '../translations/TranslationService';
import { LanguageContext } from '../context/initialContext';
import { useIsFocused } from '@react-navigation/native';

export default function Scanner() {
     const isFocused = useIsFocused();
     const [isLoading, setLoading] = React.useState(false);
     const [hasPermission, setHasPermission] = React.useState(null);
     const [scanned, setScanned] = React.useState(false);
     const { language } = React.useContext(LanguageContext);

     React.useEffect(() => {
          (async () => {
               const { status } = await BarCodeScanner.requestPermissionsAsync();
               setHasPermission(status === 'granted');
          })();
     }, []);

     const handleBarCodeScanned = ({ type, data }) => {
          console.log(data);
          setLoading(true);
          if (!scanned) {
               setScanned(true);
               navigateStack('BrowseTab', 'SearchResults', { term: data, type: 'catalog', prevRoute: 'DiscoveryScreen', scannerSearch: true });
               setLoading(false);
          } else {
               setLoading(false);
          }
     };

     if (hasPermission === null) {
          return loadingSpinner(getTermFromDictionary(language, 'scanner_request_permissions'));
     }

     if (hasPermission === false) {
          return loadError(getTermFromDictionary(language, 'scanner_denied_permissions'));
     }

     if (isLoading) {
          return loadingSpinner();
     }

     return (
          <View style={{ flex: 1, flexDirection: 'column', justifyContent: 'flex-end' }}>
               {isFocused && (
                    <>
                         <BarCodeScanner onBarCodeScanned={scanned ? undefined : handleBarCodeScanned} style={[StyleSheet.absoluteFillObject, styles.container]} barCodeTypes={[BarCodeScanner.Constants.BarCodeType.upc_a, BarCodeScanner.Constants.BarCodeType.upc_e, BarCodeScanner.Constants.BarCodeType.upc_ean, BarCodeScanner.Constants.BarCodeType.ean13, BarCodeScanner.Constants.BarCodeType.ean8]}>
                              <BarcodeMask edgeColor="#62B1F6" showAnimatedLine={false} />
                         </BarCodeScanner>
                         {scanned && (
                              <Center pb={20}>
                                   <Button onPress={() => setScanned(false)}>{getTermFromDictionary(language, 'scan_again')}</Button>
                              </Center>
                         )}
                    </>
               )}
          </View>
     );
}

const styles = StyleSheet.create({
     container: {
          flex: 1,
          alignItems: 'center',
          justifyContent: 'center',
     },
});