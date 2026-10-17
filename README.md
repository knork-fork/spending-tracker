# spending-tracker

A web app for tracking personal spending, with invite-only signup.

## Setup

```
docker-compose up -d --build
docker/composer install
./db/init-db.sh
./scripts/setup.sh
```

Optionally run `./scripts/enable_invite_only.sh` to require an invite link for signup.
