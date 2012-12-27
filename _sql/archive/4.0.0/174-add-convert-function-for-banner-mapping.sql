UPDATE s_library_component SET convert_function = 'getBannerMappingLinks' WHERE s_library_component.name= 'Banner';
-- //@UNDO
UPDATE s_library_component SET convert_function = '' WHERE s_library_component.name= 'Banner';
