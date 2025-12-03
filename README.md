## Шаблон Laravel приложения под Nginx Unit для K8s

### Структура приложения 

Стараемся следовать DDD Domain-Driven Design (Домен - это область интересов)\
Для нас DDD - это приориет на бизнес-логику

Это выражено в структуре приложения \
Директория qsiq (про QSIQ), роли приложения: 
- {service_name}-api (APP_ROLE = api)
- {service_name}-web (APP_ROLE = web)
- {service_name}-queue (APP_ROLE = queue)
- ...
- 
`APP_ROLE в переменных linux окружения` 

На одной кодовой базе под каждую роль запускается свой контейнер(-ы)


### Dockerfile и entrypoint


