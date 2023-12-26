if ! areComposerDependenciesInstalled ; then
  run composer install
fi

if [[ ${args[--coverage]} ]]; then
  run ssh -c "composer run test:unit:coverage"
else
  run ssh -c "composer run test:unit"
fi
