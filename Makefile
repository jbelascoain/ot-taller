.PHONY: up down fresh logs release

up:
	docker compose up -d --build

down:
	docker compose down

fresh:
	docker compose down -v
	docker compose up -d --build

logs:
	docker compose logs -f app

release:
	bash bin/build-release.sh
