SeoFilter.panel.Seoedit = function (config) {
    config = config || {};
    config.record = config.record || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        baseParams: {},
        id: 'seofilter-panel-seoedit',
        url: SeoFilter.config.connector_url,
        action: 'mgr/urls/update',
        defaults: { collapsible: false ,autoHeight: true }
        ,bodyStyle: ''
       // ,forceLayout: true
       // ,cls: 'container form-with-labels'
       // ,useLoadingMask: true
        ,listeners: {
             'setup': {fn:this.setup,scope:this},
             'success': {fn:this.success,scope:this},
            // 'failure': {fn:this.failure,scope:this},
            'beforeSubmit': {fn:this.beforeSubmit,scope:this},
        }
        ,hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('seofilter') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('seofilter_url_seoedit'),
                layout: 'anchor',
                items: [{
                    html: _('seofilter_url_seoedit_intro'),
                    cls: 'panel-desc',
                }, {
                    layout: 'form',
                    items: this.getFields(config)
                    ,labelAlign: 'top'
                    ,labelSeparator: ''
                    ,autoHeight: true
                    ,defaults: {
                        border: false
                        ,msgTarget: 'under'
                        ,width: 400
                    }
                    ,cls: 'main-wrapper'
                }]
            }]
        }]
    });
    SeoFilter.panel.Seoedit.superclass.constructor.call(this, config);
    this.getForm().setValues(this.config.record);
    this.getForm().setValues({'active':parseInt(this.config.record.active)});
    this.getForm().setValues({'custom':parseInt(this.config.record.custom)});
};
Ext.extend(SeoFilter.panel.Seoedit, MODx.FormPanel, {
    initialized: false
    ,setup: function() {
        //console.log(this);
        //this.enableBubble('change');
       // console.log(this.record);
        if (!this.initialized) {
            // this.getForm().setValues(this.record);
            // this.getForm().setValues({'active':parseInt(this.record.active)});
            // this.getForm().setValues({'custom':parseInt(this.record.custom)});
        }
        this.initialized = true;
    }

    ,beforeSubmit: function(o) {
        Ext.apply(o.form.baseParams,{"frame":1});
    }
    ,success: function(o) {
        Ext.getCmp('seoedit-btn-save').setDisabled(false);
        var object = o.result.object;
        if(o.result.message) {
            location.href = o.result.message;
        }
    }


    ,getFields: function (config) {
        return [{
            xtype: 'hidden',
            hideLabel: true,
            fieldLabel: _('seofilter_url_id'),
            anhchor: '99%',
            readOnly:true,
            name: 'id',
            id: 'seoedit-id',
            submitValue: true,
            value: config.record.id
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
                    id: 'seoedit-old_url',
                    readOnly: true,
                    style: 'background:#f9f9f9;color:#999;',
                    anchor: '99%',
                },{
                    xtype: 'xcheckbox',
                    boxLabel: _('seofilter_url_active_more'),
                    name: 'active',
                    id: 'seoedit-active',
                }]
            }, {
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [{
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_url_new_url'),
                    name: 'new_url',
                    id: 'seoedit-new_url',
                    anchor: '99%',
                },{
                    xtype: 'xcheckbox',
                    boxLabel: _('seofilter_seo_custom'),
                    name: 'custom',
                    id: 'seoedit-custom',
                }]
            }]

        },{
            layout:'column',
            border: false,
            anchor: '99%',
            items: [{
                columnWidth: 1
                ,layout:'form'
                ,defaults: { msgTarget: 'under' }
                ,border:false
                ,enableKeyEvents: true
                ,items: [{
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_seometa_title'),
                    name: 'title',
                    id: 'seoedit-title',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_seometa_h1'),
                    name: 'h1',
                    id: 'seoedit-h1',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                },{
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_seometa_h2'),
                    name: 'h2',
                    id: 'seoedit-h2',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                }, {
                    xtype: 'textarea',
                    fieldLabel: _('seofilter_seometa_description'),
                    name: 'description',
                    id: 'seoedit-description',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                }, {
                    xtype: 'textarea',
                    fieldLabel: _('seofilter_seometa_introtext'),
                    name: 'introtext',
                    id: 'seoedit-introtext',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                },{
                    xtype: 'textarea',
                    fieldLabel: _('seofilter_seometa_text'),
                    name: 'text',
                    id: 'seoedit-text',
                    anchor: '99%',
                    listeners: {
                        'keypress': function (w, e) {Ext.getCmp('seoedit-custom').setValue(true);},
                        'keyup': function (w, e) {if(e.button == 7) {Ext.getCmp('seoedit-custom').setValue(true);}}
                    }
                }, {
                    xtype: 'textarea',
                    heght:300,
                    fieldLabel: _('seofilter_seometa_content'),
                    name: 'content',
                    enableKeyEvents: true,
                    id: 'seoedit-content',
                    listeners: {
                        render: function () {
                            window.setTimeout(function() {
                                MODx.ux.Ace.replaceComponent('seoedit-content', 'text/x-smarty', 1);
                                MODx.ux.Ace.replaceTextAreas(Ext.query('.modx-richtext'));
                                Ext.getCmp('seoedit-content').setHeight(200);

                            }, 100);
                        },
                    },
                    anchor: '99%'
                }]
            }]
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
                    xtype: 'seofilter-combo-rule',
                    fieldLabel: _('seofilter_url_multi_id'),
                    name: 'multi_id',
                    id: 'seoedit-multi_id',
                    anchor: '99%',
                    allowBlank: false,
                    readOnly: true,
                    style: 'background:#f9f9f9;color:#999;',

                },{
                    xtype: 'numberfield',
                    fieldLabel: _('seofilter_url_count'),
                    name: 'count',
                    //readOnly: true,
                    id: 'seoedit-count',
                    anchor: '99%',
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_url_createdon'),
                    name: 'createdon',
                    id: 'seoedit-createdon',
                    anchor: '99%',

                }]
            }, {
                columnWidth: .5
                , layout: 'form'
                , defaults: {msgTarget: 'under'}
                , border: false
                , items: [{
                    xtype: 'seofilter-combo-resource',
                    fieldLabel: _('seofilter_url_page_id'),
                    name: 'page_id',
                    hiddenName: 'page_id',
                    id: 'seoedit-page_id',
                    anchor: '99%',
                    allowBlank: false,
                    readOnly: true,
                    style: 'background:#f9f9f9;color:#999;',
                },{
                    xtype: 'numberfield',
                    fieldLabel: _('seofilter_url_ajax'),
                    name: 'ajax',
                    id: 'seoedit-ajax',
                    //readOnly: true,
                    anchor: '99%',
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('seofilter_url_editedon'),
                    name: 'editedon',
                    id: 'seoedit-editedon',
                    anchor: '99%',
                }]
            }]

        }];
    }
});

Ext.reg('seofilter-panel-seoedit', SeoFilter.panel.Seoedit);