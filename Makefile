.PHONY: up down fresh logs release lan

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

lan:
	@echo "http://$$(hostname -I | awk '{print $$1}'):8080"
