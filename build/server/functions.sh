# Parses a property value from a java style properties file
# $1 - the path to the properties file
# $2 - the name of the property to extract
function parse_hostname() {
    local propertiesFile=$1; shift
    local property=$1;       shift

    sed '/^\#/d' ${propertiesFile} |
    grep ${property} |
    tail -n 1 |
    cut -d "=" -f2- |
    sed 's/^[[:space:]]*//;s/[[:space:]]*$//'
}

# Parses a port from a given hostname e.g. localhost:8080
# $1 - the hostname to parse port from
# $2 - default port if parsing fails
function parse_port() {
    local hostname=$1; shift
    local defaultPort=$1; shift

    local parsePortRegex="^.*?:([0-9]+)$"
    local port=""

    if [[ $hostname =~ $parsePortRegex ]] ; then
        port=${BASH_REMATCH[1]}
    else
        port=${defaultPort}
    fi

    echo $port
}

# starts the php inbuilt webserver
# $1 - path to the php binary
# $2 - the directory to run in
# $3 - the port to use
# $4 - path to router script
function start_php_server() {
    local phpBinPath=$1; shift
    local runDir=$1; shift
    local port=$1; shift
    local router=$1; shift

    cd "${runDir}"
    $phpBinPath -S 0.0.0.0:${port} ${router}
}
