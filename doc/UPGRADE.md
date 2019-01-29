Upgrade instructions
====================

Upgrade to 2.1.0
----------------

Release 2.1.0 provides implementation of `DownloadController`, in order to use it, 
routing file must be included in main routing configuration file:

### Include the routing config in your routing.yml

```yml
_netgen_enhancedezbinaryfile:
    resource: '@NetgenEnhancedBinaryFileBundle/Resources/config/routing.yml'
```
