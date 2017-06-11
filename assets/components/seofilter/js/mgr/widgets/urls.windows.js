SeoFilter.window.CreateUrls = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'seofilter-url-window-create';
    }
    Ext.applyIf(config, {
        title: _('seofilter_url_create'),
        width: 600,
        autoHeight: true,
        url: SeoFilter.config.connector_url,
        action: 'mgr/urls/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    SeoFilter.window.CreateUrls.superclass.constructor.call(this, config);
};

Ext.extend(SeoFilter.window.CreateUrls, MODx.Window, {

    getFields: function (config) {
        return [{
            layout: 'column',
            border: false,
            anchor: '100%',
            items: [{
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: _('seofilter_url_multi_id'),
                        name: 'multi_id',
                        id: config.id + '-multi_id',
                        anchor: '99%',
                        allowBlank: false,
                    },{
                        xtype: 'textfield',
                        fieldLabel: _('seofilter_url_old_url'),
                        name: 'old_url',
                        id: config.id + '-old_url',
                        anchor: '99%',
                    }, {

                        xtype: 'numberfield',
                        fieldLabel: _('seofilter_url_count'),
                        name: 'count',
                        id: config.id + '-count',
                        anchor: '99%',
                    }
                ]
            }, {
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('seofilter_url_active'),
                    name: 'active',
                    style: 'padding-top:20px;',
                    id: config.id + '-active',
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_url_new_url'),
                    name: 'new_url',
                    id: config.id + '-new_url',
                    anchor: '99%',
                }, {
                //     xtype: 'textfield',
                //     fieldLabel: _('seofilter_url_createdon'),
                //     name: 'createdon',
                //     id: config.id + '-createdon',
                //     anchor: '99%',
                // }, {
                //     xtype: 'textfield',
                //     fieldLabel: _('seofilter_url_editedon'),
                //     name: 'editedon',
                //     id: config.id + '-editedon',
                //     anchor: '99%',
                // }, {
                    xtype: 'numberfield',
                    fieldLabel: _('seofilter_url_ajax'),
                    name: 'ajax',
                    id: config.id + '-ajax',
                    anchor: '99%',
                }
                ]
            }]
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('seofilter-url-window-create', SeoFilter.window.CreateUrls);

SeoFilter.window.UpdateUrls = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'seofilter-url-window-update';
    }
    Ext.applyIf(config, {
        title: _('seofilter_url_update'),
        width: 600,
        autoHeight: true,
        url: SeoFilter.config.connector_url,
        action: 'mgr/urls/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    SeoFilter.window.UpdateUrls.superclass.constructor.call(this, config);
};
Ext.extend(SeoFilter.window.UpdateUrls, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('seofilter_url_multi_id'),
            name: 'multi_id',
            id: config.id + '-multi_id',
            anchor: '99%',
            allowBlank: false,
        }, {
            layout: 'column',
            border: false,
            anchor: '100%',
            items: [{
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [{
                        xtype: 'textfield',
                        fieldLabel: _('seofilter_url_old_url'),
                        name: 'old_url',
                        id: config.id + '-old_url',
                        readOnly: true,
                        style: 'background:#f9f9f9;color:#aaa;',
                        anchor: '99%',
                    }, {
                        xtype: 'numberfield',
                        fieldLabel: _('seofilter_url_count'),
                        name: 'count',
                        readOnly: true,
                        id: config.id + '-count',
                        anchor: '99%',
                    }
                ]
            }, {
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [{
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_url_new_url'),
                    name: 'new_url',
                    id: config.id + '-new_url',
                    anchor: '99%',
                }, {
                //         xtype: 'textfield',
                //         fieldLabel: _('seofilter_url_createdon'),
                //         name: 'createdon',
                //         id: config.id + '-createdon',
                //         anchor: '99%',
                //     }, {
                //         xtype: 'textfield',
                //         fieldLabel: _('seofilter_url_editedon'),
                //         name: 'editedon',
                //         id: config.id + '-editedon',
                //         anchor: '99%',
                // }, {
                    xtype: 'numberfield',
                    fieldLabel: _('seofilter_url_ajax'),
                    name: 'ajax',
                    id: config.id + '-ajax',
                    readOnly: true,
                    anchor: '99%',
                    }
                ]
            }]
        },{
            xtype: 'xcheckbox',
            boxLabel: _('seofilter_url_active_more'),
            name: 'active',
            style: 'padding-top:20px;position:relative;',
            id: config.id + '-active',
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('seofilter-url-window-update', SeoFilter.window.UpdateUrls);