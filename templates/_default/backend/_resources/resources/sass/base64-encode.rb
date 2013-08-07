require 'compass'
require 'sass'
require 'base64'

module Sass::Script::Functions

  def base64Encode(string)
    assert_type string, :String
    Sass::Script::String.new(Base64.encode64(string.value))
  end

  declare :base64Encode, :args => [:string]
end

# Add the function to the compass framework to support older compass / sass / ruby versions
module Compass::SassExtensions::Functions::Base64Encode
   include Sass::Script::Functions
end