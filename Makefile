# You have to define the values in {}
APP_NAME=nano-sekelton
VERSION=1.0
DOCKER_REPO=xtwoend

PORT=8080
DIST_PORT=8080

# DOCKER TASKS
# Build the container
build: ## Build the container
	docker build -t $(APP_NAME) .

build-nc: ## Build the container without caching
	docker build --no-cache -t $(APP_NAME) .

run: ## Run container
	docker run -i -t --rm --env-file .env-docker -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

run-dev: ## Run container dev volume
	docker run -i -t --rm --env-file .env-docker -v $(PWD)/:/var/www/app -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

up: build run ## Run container on port 

up-dev: build run-dev

stop: ## Stop and remove a running container
	docker stop $(APP_NAME); docker rm $(APP_NAME)

release: build-nc publish

# Docker publish
publish: publish-latest publish-version ## Publish the `{version}` ans `latest` tagged containers to ECR

publish-latest: tag-latest ## Publish the `latest` taged container to ECR
	@echo 'publish latest to $(DOCKER_REPO)'
	docker push $(DOCKER_REPO)/$(APP_NAME):latest

publish-version: tag-version ## Publish the `{version}` taged container to ECR
	@echo 'publish $(VERSION) to $(DOCKER_REPO)'
	docker push $(DOCKER_REPO)/$(APP_NAME):$(VERSION)

# Docker tagging
tag: tag-latest tag-version ## Generate container tags for the `{version}` ans `latest` tags

tag-latest: ## Generate container `{version}` tag
	@echo 'create tag latest'
	docker tag $(APP_NAME) $(DOCKER_REPO)/$(APP_NAME):latest

tag-version: ## Generate container `latest` tag
	@echo 'create tag $(VERSION)'
	docker tag $(APP_NAME) $(DOCKER_REPO)/$(APP_NAME):$(VERSION)


