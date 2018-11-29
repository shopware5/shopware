{block name="backend/index/menu/function"}
    {function name=backend_menu level=0}
        [{foreach $menu as $category}
            {$isNotDetailAction=$category['action'] && $category['action']|lower !== 'detail'}
            {$isDetailActionAndHasPrivilege=$category['action']|lower == 'detail' && {acl_is_allowed privilege=create resource=$category['controller']|lower}}
            {$hasReadPrivilegeForController={acl_is_allowed privilege=read resource=$category['controller']|lower}}

            {if ($category['onclick'] || $isNotDetailAction || $isDetailActionAndHasPrivilege || $category['children']) && $hasReadPrivilegeForController}
            {
                {if $level === 0}{if $category['children']}xtype: 'hoverbutton',{else}xtype: 'button',{/if}{/if}
                {$name = null}
                {if $category['controller']}{$name = $category['controller']}{/if}
                {if $category['action'] && $category['action'] != 'Index'}{$name = "{$category['controller']}/{$category['action']}"}{/if}
                text: "{if $name}{$category['label']|unescape|snippet:$name:'backend/index/view/main'}{else}{$category['label']|unescape}{/if}{if $category['shortcut']}&nbsp;<span class='shortcut'>({$category['shortcut']|snippet:$name:'backend/index/view/shortcuts'})</span>{/if}",
                {if $category['controller'] && $category['action']}handler: function() {
                    Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.{$category['controller']}',
                    localizedName: "{if $name}{$category['label']|unescape|snippet:$name:'backend/index/view/main'}{else}{$category['label']|unescape}{/if}"
                    {if $category['action'] && $category['action'] != 'Index'}, action: '{$category['action']}'{/if}
                    });
                    },
                {/if}
                {if $category['onclick']}handler: function() { {$category['onclick']} },{/if}
                {if $category['label']|unescape|substr:-1 == '*'}cls: Ext.baseCSSPrefix +'deprecated-menu-item', overCls: Ext.baseCSSPrefix +'deprecated-menu-item-active',{/if}
                iconCls: "{$category['class']}"{if $category['children']},
                menu: Ext.create('Ext.menu.Menu', {
                shadow: false, cls: 'shopware-ui-main-menu',
                showSeparator: false, plain: true, ui: 'shopware-ui', margin: '0 0 0 2',
                items: {call name=backend_menu menu=$category['children'] level=$level+1}
                })
            {/if}
                }{if !$category@last},{ xtype: 'tbspacer', width: 6 }, { xtype: 'tbseparator' }, { xtype: 'tbspacer', width: 6 }, {/if}
            {/if}
        {/foreach}]
    {/function}
{/block}
{call name=backend_menu menu=$menu}
