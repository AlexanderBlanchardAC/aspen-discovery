<form id="selectWebBuilderTemplateForm" class="form-horizontal" role="form">
    <input type="hidden" name="templateId" id="templateId" value="{$templateId}" />

    <div class="form-group col-xs-12">
        <label for="templateName" class="control-label">{translate text="Template" isAdminFacing=true}</label>
        <select id="templateName" name="templateName" class="form-control">
            <option value="-1">{translate text="None" isAdminFacing=true}</option>
            {foreach from=$templates item=template}
                <option value="{$template.templateId}">{translate text=$template.templateName isAdminFacing=true}</option>
            {/foreach}
        </select>
        </div>
    <div class="form-group col-xs-12">
    
    </div>
</form>