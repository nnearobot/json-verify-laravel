build:
	./vendor/bin/sail build
	$(MAKE) start

start:
	./vendor/bin/sail up -d
	# ===================================
	# Frontend is here: http://localhost:4173
	#
	# API server is here: http://localhost:80
	# ===================================

stop:
	./vendor/bin/sail stop
	# ===================================
	# Verification server has been stopped
	# ===================================

test:
	./vendor/bin/sail artisan test
	docker compose exec -it laravel.test ./vendor/bin/phpunit --coverage-text
