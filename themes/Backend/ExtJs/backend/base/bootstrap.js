/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

{* Include default components *}
{include file='ExtJs/components/Enlight.app.Window.js'}
{include file='ExtJs/components/Enlight.app.SubWindow.js'}
{include file='ExtJs/components/Enlight.app.SubApplication.js'}
{include file='ExtJs/components/Enlight.app.Controller.js'}
{include file='ExtJs/components/Ext.util.FileUpload.js'}
{include file='ExtJs/components/Enlight.app.WindowManagement.js'}
{include file='ExtJs/components/Enlight.form.mixin.HelpSupportElements.js'}
{include file='ExtJs/components/Enlight.app.SubWindow.js'}
{include file='ExtJs/components/Ext.ux.DataView.DragSelector.js'}
{include file='ExtJs/components/Ext.ux.DataView.LabelEditor.js'}
{include file='ExtJs/components/Ext.ux.form.field.BoxSelect.js'}
{include file='ExtJs/components/Ext.ux.RowExpander.js'}
{include file='ExtJs/components/Ext.ux.form.MultiSelect.js'}
{include file='ExtJs/components/Ext.ux.form.ItemSelector.js'}

{* Include overrides *}
{include file='ExtJs/overrides/Ext.Base.js'}
{include file='ExtJs/overrides/Ext.grid.header.Container.js'}
{include file='ExtJs/overrides/Ext.ClassManager.js'}
{include file='ExtJs/overrides/Ext.data.proxy.Server.js'}
{include file='ExtJs/overrides/Ext.app.Application.js'}
{include file='ExtJs/overrides/Ext.app.Controller.js'}
{include file='ExtJs/overrides/Ext.Loader.js'}
{include file='ExtJs/overrides/Ext.app.EventBus.js'}
{include file='ExtJs/overrides/Ext.button.Button.js'}
{include file='ExtJs/overrides/Ext.LoadMask.js'}
{include file='ExtJs/overrides/Ext.form.Field.js'}
{include file='ExtJs/overrides/Ext.form.field.HtmlEditor.js'}
{include file='ExtJs/overrides/Ext.toolbar.Paging.js'}
{include file='ExtJs/overrides/Ext.Template.js'}
{include file='ExtJs/overrides/Ext.form.Base.js'}
{include file='ExtJs/overrides/Ext.data.writer.Json.js'}
{include file='ExtJs/overrides/Ext.grid.column.Action.js'}
{include file='ExtJs/overrides/Ext.view.BoundList.js'}
{include file='ExtJs/overrides/Ext.form.field.ComboBox.js'}
{include file='ExtJs/overrides/Ext.form.field.Time.js'}
{include file='ExtJs/overrides/Ext.form.field.Number.js'}
{include file='ExtJs/overrides/Ext.tree.Panel.js'}
{include file='ExtJs/overrides/Ext.panel.Panel.js'}
{include file='ExtJs/overrides/Ext.ZIndexManager.js'}
{include file='ExtJs/overrides/Ext.MessageBox.js'}
{include file='ExtJs/overrides/Ext.grid.RowEditor.js'}
{include file='ExtJs/overrides/Ext.picker.Date.js'}
{include file='ExtJs/overrides/Ext.data.association.HasMany.js'}
{include file='ExtJs/overrides/Ext.menu.Menu.js'}
{include file='ExtJs/overrides/Ext.Timeout.js'}
{include file='ExtJs/overrides/Ext.Ajax.js'}
{include file='ExtJs/overrides/Ext.container.DockingContainer.js'}
{include file='backend/base/component/Shopware.data.reader.Application.js'}
{include file='ExtJs/overrides/Ext.form.field.Display.js'}
{include file='ExtJs/overrides/Ext.String.js'}
{include file='ExtJs/overrides/Ext.view.Table.js'}

//Shopware backend application components
{include file='backend/base/application/Shopware.model.Helper.js'}
{include file='backend/base/application/Shopware.grid.Controller.js'}
{include file='backend/base/application/Shopware.grid.Panel.js'}
{include file='backend/base/application/Shopware.data.Model.js'}
{include file='backend/base/application/Shopware.store.Listing.js'}
{include file='backend/base/application/Shopware.window.Detail.js'}
{include file='backend/base/application/Shopware.window.Listing.js'}
{include file='backend/base/application/Shopware.window.Progress.js'}
{include file='backend/base/application/Shopware.model.DataOperation.js'}
{include file='backend/base/application/Shopware.grid.Association.js'}
{include file='backend/base/application/Shopware.model.Container.js'}
{include file='backend/base/application/Shopware.form.field.Search.js'}
{include file='backend/base/application/Shopware.detail.Controller.js'}
{include file='backend/base/application/Shopware.listing.InfoPanel.js'}
{include file='backend/base/application/Shopware.listing.FilterPanel.js'}
{include file='backend/base/application/Shopware.filter.Field.js'}
{include file='backend/base/application/Shopware.store.Association.js'}
{include file='backend/base/application/Shopware.form.field.Media.js'}
{include file='backend/base/application/Shopware.store.Search.js'}

{* Include global models *}
{include file='backend/base/model/user.js'}
{include file='backend/base/model/category.js'}
{include file='backend/base/model/customer_group.js'}
{include file='backend/base/model/dispatch.js'}
{include file='backend/base/model/payment.js'}
{include file='backend/base/model/shop.js'}
{include file='backend/base/model/supplier.js'}
{include file='backend/base/model/country.js'}
{include file='backend/base/model/article.js'}
{include file='backend/base/model/variant.js'}
{include file='backend/base/model/locale.js'}
{include file='backend/base/model/currency.js'}
{include file='backend/base/model/payment_status.js'}
{include file='backend/base/model/order_status.js'}
{include file='backend/base/model/address.js'}
{include file='backend/base/model/billing_address.js'}
{include file='backend/base/model/customer.js'}
{include file='backend/base/model/tax.js'}
{include file='backend/base/model/media.js'}
{include file='backend/base/model/template.js'}
{include file='backend/base/model/country_area.js'}
{include file='backend/base/model/country_state.js'}
{include file='backend/base/model/form.js'}
{include file='backend/base/model/element.js'}
{include file='backend/base/model/value.js'}
{include file='backend/base/model/position_status.js'}
{include file='backend/base/model/doc_type.js'}
{include file='backend/base/model/password_encoder.js'}
{include file='backend/base/model/captcha.js'}
{include file='backend/base/model/product_box_layout.js'}
{include file='backend/base/model/page_not_found_destination_options.js'}
{include file='backend/base/model/attribute_config.js'}
{include file='backend/base/model/corner_position.js'}
{include file='backend/base/model/cookie_mode.js'}

{* Include global stores *}
{include file='backend/base/store/user.js'}
{include file='backend/base/store/category.js'}
{include file='backend/base/store/category_tree.js'}
{include file='backend/base/store/customer_group.js'}
{include file='backend/base/store/dispatch.js'}
{include file='backend/base/store/doc_type.js'}
{include file='backend/base/store/payment.js'}
{include file='backend/base/store/shop.js'}
{include file='backend/base/store/shop_language.js'}
{include file='backend/base/store/translation.js'}
{include file='backend/base/store/supplier.js'}
{include file='backend/base/store/country.js'}
{include file='backend/base/store/article.js'}
{include file='backend/base/store/variant.js'}
{include file='backend/base/store/locale.js'}
{include file='backend/base/store/currency.js'}
{include file='backend/base/store/payment_status.js'}
{include file='backend/base/store/order_status.js'}
{include file='backend/base/store/tax.js'}
{include file='backend/base/store/template.js'}
{include file='backend/base/store/country_area.js'}
{include file='backend/base/store/country_state.js'}
{include file='backend/base/store/form.js'}
{include file='backend/base/store/position_status.js'}
{include file='backend/base/store/password_encoder.js'}
{include file='backend/base/store/captcha.js'}
{include file='backend/base/store/product_box_layout.js'}
{include file='backend/base/store/listing_filter_mode.js'}
{include file='backend/base/store/page_not_found_destination_options.js'}
{include file='backend/base/store/attribute_config.js'}
{include file='backend/base/store/salutation.js'}
{include file='backend/base/store/corner_position.js'}
{include file='backend/base/store/cookie_mode.js'}


{* Include shopware related components *}
{include file='backend/base/component/Shopware.button.HoverButton.js'}
{include file='backend/base/component/Shopware.MediaManager.MediaSelection.js'}
{include file='backend/base/component/Shopware.MediaManager.MediaTextSelection.js'}
{include file='backend/base/component/Shopware.VTypes.js'}
{include file='backend/base/component/Shopware.form.field.TinyMCE.js'}
{include file='backend/base/component/Shopware.form.plugin.Translation.js'}
{include file='backend/base/component/Shopware.form.plugin.SnippetTranslation.js'}
{include file='backend/base/component/Shopware.grid.HeaderToolTip.js'}
{include file='backend/base/component/Shopware.Notification.js'}
{include file='backend/base/component/Shopware.form.PasswordStrengthMeter.js'}
{include file='backend/base/component/Shopware.form.field.AceEditor.js'}
{include file='backend/base/component/Shopware.form.field.ArticleSearch.js'}
{include file='backend/base/component/Shopware.form.field.PagingComboBox.js'}
{include file='backend/base/component/Shopware.container.Viewport.js'}
{include file='backend/base/component/Shopware.DragAndDropSelector.js'}
{include file='backend/base/component/Shopware.DataView.GooglePreview.js'}
{include file='backend/base/component/Shopware.form.field.ComboTree.js'}
{include file='backend/base/component/Shopware.window.plugin.Hub.js'}
{include file='backend/base/component/Shopware.grid.plugin.Translation.js'}
{include file='backend/base/component/Shopware.form.PluginPanel.js'}
{include file='backend/base/component/Shopware.component.Preloader.js'}
{include file='backend/base/component/Shopware.component.IconPreloader.js'}
{include file='backend/base/component/Shopware.global.ErrorReporter.js'}
{include file='backend/base/component/Shopware.notification.SubscriptionWarning.js'}
{include file='backend/base/component/Shopware.component.ValidatePassword.js'}
{include file='backend/base/component/Shopware.window.ExpiredPluginWarning.js'}
{include file='backend/base/component/Shopware.window.ExpiredPluginStop.js'}

{include file='backend/base/component/Shopware.form.field.ColorField.js'}
{include file='backend/base/component/Shopware.form.field.ColorSelection.js'}
{include file='backend/base/component/Shopware.color.Window.js'}
{include file='backend/base/component/Shopware.grid.ButtonColumn.js'}
{include file='backend/base/component/Shopware.window.SimpleModule.js'}

{include file='backend/base/component/element/boolean.js'}
{include file='backend/base/component/element/boolean_select.js'}
{include file='backend/base/component/element/button.js'}
{include file='backend/base/component/element/color.js'}
{include file='backend/base/component/element/date.js'}
{include file='backend/base/component/element/date_time.js'}
{include file='backend/base/component/element/fieldset.js'}
{include file='backend/base/component/element/html.js'}
{include file='backend/base/component/element/interval.js'}
{include file='backend/base/component/element/number.js'}
{include file='backend/base/component/element/select.js'}
{include file='backend/base/component/element/select_tree.js'}
{include file='backend/base/component/element/text.js'}
{include file='backend/base/component/element/textarea.js'}
{include file='backend/base/component/element/time.js'}
{include file='backend/base/component/element/media_selection.js'}
{include file='backend/base/component/element/product_box_layout_select.js'}
{include file='backend/base/component/element/listing_filter_mode_select.js'}
{include file='backend/base/component/element/media_text_selection.js'}
{include file='backend/base/component/Shopware.ModuleManager.js'}


// Shopware attribute components
{include file='backend/base/attribute/mixins/Shopware.attribute.SelectionFactory.js'}

{include file='backend/base/attribute/model/dynamic.js'}
{include file='backend/base/attribute/model/dynamic_reader.js'}



{include file='backend/base/attribute/Shopware.attribute.FieldHandlerInterface.js'}

{include file='backend/base/attribute/field_handler/Shopware.attribute.BooleanFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.DateFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.DateTimeFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.FloatFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.HtmlFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.IntegerFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.SingleSelectionFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.MultiSelectionFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.StringFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.TextAreaFieldHandler.js'}

{include file='backend/base/attribute/field/Shopware.form.field.Grid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.GridView.js'}
{include file='backend/base/attribute/field/Shopware.form.field.SingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.MediaGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PropertyOptionGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.BlogGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CountryGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CategoryGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.BlogSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CountrySingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PropertyOptionSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CategorySingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.VoucherSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.VoucherGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductFeedGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductFeedSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.NewsletterGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.NewsletterSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PartnerGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PartnerSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.FormGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.FormSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomerGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomerSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomerStreamGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomerStreamSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.DispatchGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PaymentGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PaymentSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.MailGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.MailSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.EmotionGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.EmotionSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PremiumGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.PremiumSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductStreamGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ProductStreamSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ShopGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomSortingGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.CustomFacetGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.AttributeSingleSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.OrderDetailGrid.js'}
{include file='backend/base/attribute/field/Shopware.form.field.OrderDetailSingleSelection.js'}
{include file='backend/base/component/Shopware.form.field.ProductStreamSelection.js'}
{include file='backend/base/attribute/field/Shopware.form.field.ContentTypeSelection.js'}

{include file='backend/base/attribute/field_handler/Shopware.attribute.AbstractEntityFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.CategoryFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.BlogFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.CountryFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.ProductFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.PropertyOptionFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.MediaFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.VoucherFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.ProductFeedFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.NewsletterFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.PartnerFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.FormFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.CustomerFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.CustomerStreamFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.DispatchFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.PaymentFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.MailFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.EmotionFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.PremiumFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.ProductStreamFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.ShopFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.ComboBoxFieldHandler.js'}
{include file='backend/base/attribute/field_handler/Shopware.attribute.OrderDetailFieldHandler.js'}

{include file='backend/base/attribute/Shopware.attribute.Form.js'}
{include file='backend/base/attribute/Shopware.attribute.Window.js'}
{include file='backend/base/attribute/Shopware.attribute.Button.js'}
{include file='backend/base/attribute/Shopware.grid.plugin.Attribute.js'}

{include file='backend/base/model/custom_sorting.js'}
{include file='backend/base/store/custom_sorting.js'}
{include file='backend/base/model/custom_facet.js'}
{include file='backend/base/store/custom_facet.js'}

{include file='backend/base/application/Shopware.helper.BatchRequests.js'}