//{namespace name="backend/benchmark/categories"}
//{block name="backend/benchmark/store/industry"}
Ext.define('Shopware.apps.Benchmark.store.Industry', {
    extend : 'Ext.data.Store',
    autoLoad : false,

    fields: [
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string'}
    ],

    data: [
        { id: 0, name: '{s name="none_selected"}None selected yet{/s}'},
        { id: 1, name: '{s name="animals_pet_supplies"}Animals & Pet Supplies{/s}'},
        { id: 2, name: '{s name="apparel_Accessories"}Apparel & Accessories{/s}'},
        { id: 3, name: '{s name="arts_entertainment"}Arts & Entertainment{/s}'},
        { id: 4, name: '{s name="baby_toddler"}Baby & Toddler{/s}'},
        { id: 5, name: '{s name="business_industrial"}Business & Industrial{/s}'},
        { id: 6, name: '{s name="cameras_optics"}Cameras & Optics{/s}'},
        { id: 7, name: '{s name="electronics"}Electronics{/s}'},
        { id: 8, name: '{s name="food_beverages_tobacco"}Food, Beverages & Tobacco{/s}'},
        { id: 9, name: '{s name="furniture"}Furniture{/s}'},
        { id: 10, name: '{s name="hardware"}Hardware{/s}'},
        { id: 11, name: '{s name="health_beauty"}Health & Beauty{/s}'},
        { id: 12, name: '{s name="home_garden"}Home & Garden{/s}'},
        { id: 13, name: '{s name="luggage_bags"}Luggage & Bags{/s}'},
        { id: 14, name: '{s name="mature"}Mature{/s}'},
        { id: 15, name: '{s name="media"}Media{/s}'},
        { id: 16, name: '{s name="office_supplies"}Office Supplies{/s}'},
        { id: 17, name: '{s name="religious_ceremonial"}Religious & Ceremonial{/s}'},
        { id: 18, name: '{s name="software"}Software{/s}'},
        { id: 19, name: '{s name="sporting_goods"}Sporting Goods{/s}'},
        { id: 20, name: '{s name="toys_games"}Toys & Games{/s}'},
        { id: 21, name: '{s name="vehicles_parts"}Vehicles & Parts{/s}'}
    ]
});
//{/block}
