.PHONY: test code

test:
	./vendor/bin/peridot test
code:
	php make-code.php
docs:
	head -n $$(grep -n '## API' README.md | cut -f1 -d:) README.md > _tmp_readme.bak
	./vendor/bin/peridot -r peridocs test >> _tmp_readme.bak
	mv _tmp_readme.bak README.md
