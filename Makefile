.PHONY: test code

test:
	./vendor/bin/peridot test
code:
	php make-consts.php
	php make-curried.php
