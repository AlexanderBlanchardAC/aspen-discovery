AspenDiscovery.CommunityEngagement = function() {
    return {
        campaignRewardGiven: function(userId, campaignId) {
            var url = Globals.path + "/Community/AJAX?method=campaignRewardGivenUpdate";
            var params = {
                userId: userId, 
                campaignId: campaignId,
            };
            $.getJSON(url, params, 
                function(data) {
                    if (data.success) {
                        var button = $('.set-reward-btn[data-user-id="' + userId + '"][data-campaign-id="' + campaignId + '"]');
                        button.replaceWith('<span>Reward Given</span>');
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
               
                alert('An error occurred while updating the reward status.' + textStatus + ', ' + errorThrown);
                });
        },
        milestoneRewardGiven: function(userId, campaignId, milestoneId) {
            var url = Globals.path + "/Community/AJAX?method=milestoneRewardGivenUpdate";
            var params = {
                userId: userId,
                campaignId: campaignId,
                milestoneId: milestoneId,
            };
            $.getJSON(url, params,
                function(data) {
                    if (data.success) {
                        var button = $('.set-reward-btn-milestone[data-user-id="' + userId + '"][data-campaign-id="' + campaignId + '"][data-milestone-id="' + milestoneId + '"]');
                        button.replaceWith('<span>Milestone Reward Given</span>');
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    alert('An error occurred while updating the reward status for this milestone.' + textStatus + ', ' + errorThrown);
                });
        },
        filterDropdownOptions: function(filterType) {

           var selectedId = (filterType === 'campaign') ? document.getElementById("campaign_id").value : document.getElementById("user_id").value;

            var url = Globals.path + "/Community/AJAX?method=filterCampaigns";
            var params = {
                campaignId: filterType === "campaign" ? selectedId : null,
                userId: filterType === "user" ? selectedId : null
            }
            
            //Show/hide campaigns list and filtered campaigns divs
            var campaignsList = document.getElementById("campaignsList");
            var filteredCampaign = document.getElementById("filteredCampaign");

     
            $.getJSON(url, params, 
                function(data) {
                    if (data.success) {
                        $('#filteredCampaign').html(data.html);
                        filteredCampaign.style.display = "block"; 
                        campaignsList.style.display = "none";
                    } else {
                        alert("Error:" +  data.message);
                    }
                })
                .fail(function() {
                    console.error('Error retrieving campaign data.');
                });
        
        },
        filterLeaderboard: function() {
            var selectedCampaignId = document.getElementById("campaign_id").value;
            var url = Globals.path + "/Community/AJAX?method=filterLeaderboardCampaigns";
            var params = {
                campaignId: selectedCampaignId
            }


           $.getJSON(url, params, function(data) {
                if (data.success) {
                    if (data.message) {
                        $('#leaderboard-table').html('<p>' + data.message + '</p>');
                    } else {
                        $('#leaderboard-table').html(data.html);
                    }
                } else {
                    console.log("Failed to retrieve leaderboard data");
                }
           }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
           });
        },
        customizeLeaderboard: function() {
            var url = Globals.path + "/Community/AJAX?method=getLeaderboardPage";
            var gjs = document.getElementById("gjs");

            $.get(url, function(data) {
                if (data.success) {
                    AspenDiscovery.CommunityEngagement.initGrapesEditor(data.html, data.css);
                    gjs.style.display = "block"; 
                } else {
                    alert("Failed to load leaderboard data: " + data.message);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log('Ajax request failed', jqXHR, textStatus, errorThrown);
                AspenDiscovery.ajaxFail(jqXHR, textStatus, errorThrown);
            })
        },
        initGrapesEditor: function(html, css) {
            try {
                var editor = grapesjs.init({
                    container: '#gjs',
                    storageManager: { autoload: false },
                    components: html,
                    style: css,
                    plugins: [
                        'grapesjs-preset-webpage'
                    ],
                    pluginsOpts: {
                        'grapesjs-preset-webpage': {}
                    }
                });
                editor.Panels.addButton('options', [{
                    id: 'save-updated-leaderboard-page',
                    className: 'fas fa-save',
                    command: 'save-updated-leaderboard-page',
                    attributes: {
                        title: 'Save Leaderboard'
                    }
                }]);
                editor.Commands.add('save-updated-leaderboard-page', {
                    run: function (editor, sender) {
                        sender && sender.set('active', 0);

                        var updatedHtml = editor.getHtml();
                        var updatedCss = editor.getCss();
                    

                        $.ajax({
                            url: Globals.path + 'WebBuilder/AJAX?method=saveUpdatedLeaderboardPage',
                            type: 'POST',
                            data: {
                                html: updatedHtml,
                                css: updatedCss
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('Leaderboard page updated successfully!');
                                    document.getElementById("gjs").style.display = "none";
                                    AspenDiscovery.CommunityEngagement.refreshLeaderboardPage();
                                } else {
                                    alert("Failed to save leaderboard page: " + response.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert('Error saving leaderboard: ' + textStatus);
                            }
                        })
                    }
                })
                editor.on('load', () => {
               
                })
            } catch (e) {
                console.error('Error initializing GrapesJS editor: ', e);
            }
        },
        refreshLeaderboardPage: function() {
            var url = Globals.path + "/Community/AJAX?method=getUpdatedLeaderboardPage";

            $.get(url, function(data) {
                console.log("Server Response: ", data);
                if (data.success) {
                    $('#leaderboard-main-content').html(data.html);
                    var style = document.createElement('style');
                    style.innerHTML = data.css;
                    document.head.appendChild(style);
                } else {
                    alert("Failed to load leaderboard data after save: " + data.message);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log('Ajax request failed', jqXHR, textStatus, errorThrown);
                AspenDiscovery.ajaxFail(jqXHR, textStatus, errorThrown);
            });
        }
    }
    
}(AspenDiscovery.CommunityEngagement || {});