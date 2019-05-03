# Invest Document sharing site

This is a prototype platform to share our investment documents
Detailed documentation will follow, for now it's just internal notes.

# ENV Variables etc.
You can configure a lot of stuff through ENV-Variables. Here's how.


## Zapier

Zapier captures a few things, if you want to change the hook, here's how:
```bash 
ZAPIER_HOOK_URL=/hooks/catch/1234/blahd3d/
```

## Varia
There are a lot more variables that you can set, they are pretty obiously named...
```bash
JWT_SECRET=random secret for jwt etc.
ZAPIER_SECRET=random secret for zapier hooks
SITE_ENV
DB_USERNAME
DB_NAME
DB_HOST
DB_PASSWORD
```
