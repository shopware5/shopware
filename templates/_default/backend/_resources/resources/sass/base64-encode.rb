require 'sass'
require 'base64'

module Compass::SassExtensions::Functions::Base64Encode

    def base64Encode(string)
        assert_type string, :String
        Sass::Script::String.new(Base64.encode64(string.value))
    end

    declare :base64Encode, :args => [:string]
end