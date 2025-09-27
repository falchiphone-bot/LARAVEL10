# Laravel Application Docker Image

Esta imagem Docker contém uma aplicação Laravel completa com nginx, PHP-FPM e todas as dependências necessárias.

## Características

- **Base**: PHP 8.1-FPM (Debian Bookworm)
- **Servidor Web**: Nginx 1.22.1
- **Gerenciador de Processos**: Supervisord
- **Extensões PHP**: 
  - mbstring, pdo_mysql, zip, exif, pcntl, gd, memcached
  - sqlsrv, pdo_sqlsrv (SQL Server)
- **Composer**: Última versão
- **Node.js**: Para build de assets

## Componentes

### Serviços Rodando
- Nginx (porta 80)
- PHP-FPM (porta 9000)
- Laravel Queue Worker
- Laravel Scheduler

### Volumes Recomendados
```yaml
volumes:
  - ./app:/var/www
  - ./storage:/var/www/storage
  - /external-disk:/mnt/external
```

### Portas Expostas
- `80`: HTTP (Nginx)
- `83`: Status/Monitoramento
- `4040`: Aplicação customizada

## Uso

### Docker Compose
```yaml
services:
  app:
    image: falchiprf/laravel-app:latest
    ports:
      - "82:80"
      - "83:83"
      - "4040:4040"
    volumes:
      - .:/var/www:delegated
    networks:
      - proxy
```

### Docker Run
```bash
docker run -p 82:80 -v $(pwd):/var/www falchiprf/laravel-app:latest
```

## Comandos Úteis

### Executar Comandos Artisan
```bash
docker exec <container> php artisan migrate
docker exec <container> php artisan optimize:clear
```

### Acessar Container
```bash
docker exec -it <container> bash
```

## Configuração

### Variáveis de Ambiente
- `APP_ENV`: local/production
- `APP_DEBUG`: true/false
- `DB_*`: Configurações do banco de dados

### SSL/HTTPS
A imagem suporta certificados SSL personalizados via:
- `/data/custom_ssl/`
- Let's Encrypt (com Nginx Proxy Manager)

## Domínios Suportados

Esta imagem foi configurada para suportar:
- contabilidade.falchi.com.br
- tanabisaf.com.br
- vec.org.br

## Tags Disponíveis

- `latest`: Última versão estável
- `2025-09-27`: Versão específica
- `production`: Para ambiente de produção

## Monitoramento

### Status Endpoints
- `/status.nginx`: Status do Nginx
- `/fmp_status`: Status do PHP-FMP

### Logs
- Nginx: `/var/log/nginx/`
- PHP: `/var/log/php/`
- Laravel: `/var/www/storage/logs/`

## Construção

Para construir a imagem localmente:
```bash
docker build -t laravel-app .
```

## Suporte

Para suporte e issues, acesse o repositório GitHub do projeto.

---

**Autor**: falchiprf  
**Licença**: MIT  
**Última Atualização**: 27/09/2025
