if [[ -z ${ES_IMAGE} ]]; then
    exit 0
fi

docker pull ${ES_IMAGE}
docker run -it --name=elasticsearch -e "discovery.type=single-node" -d -p 9200:9200 ${ES_IMAGE}
