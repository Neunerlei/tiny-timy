CMD="${other_args[*]}"

if [[ "${CMD}" == install* || "${CMD}" == update* || "${CMD}" == require* || "${CMD}" == remove* ]]; then
  CMD="${CMD} --ignore-platform-req=ext-memcached"
fi

run ssh -c "composer ${CMD}"
