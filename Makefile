ASSETS := \
	assets/badge.min.css \
	assets/badge.min.css.br \
	assets/badge.min.css.gz

.PHONY: all php js clean dist-clean
all: php js

php: vendor

js: $(ASSETS)

dist-clean: clean
	rm -rf composer.phar vendor node_modules

clean:
	rm -rf assets/*.css assets/*.css.gz assets/*.css.br

vendor: composer.lock composer.phar
	./composer.phar install --prefer-dist

composer.lock: composer.json composer.phar
	./composer.phar update --prefer-dist

composer.phar:
	curl -sSL 'https://getcomposer.org/installer' | php -- --stable
	touch -r composer.json $@

node_modules: package-lock.json
	npm install

package-lock.json: package.json
	npm update

.PRECIOUS: %.css
%.css: %.sass node_modules
	npx sass --indented --no-source-map --quiet $< | \
		npx postcss --no-map --use autoprefixer -o $@

.PRECIOUS: %.min.css
%.min.css: %.css node_modules
	npx cleancss -o $@ $<

%.gz: %
	gzip -9 < $< > $@

BROTLI := $(shell if [ -e /usr/bin/brotli ]; then echo brotli; else echo bro; fi )
%.br: %
ifeq ($(BROTLI),bro)
	bro --quality 11 --force --input $< --output $@ --no-copy-stat
else
	brotli -Zfo $@ $<
endif
	@chmod 644 $@
	@touch $@
