# Color Palettes

## Installation

To use this platform, you need:

- [Git](https://git-scm.com/)
- [Composer](https://getcomposer.org)
- [Compass](https://compass-style.org) which includes SASS when installing it

### Install needed components

#### Compass configuration

The configuration file ```config.rb``` for Compass relies in the root folder.  
Just run ``compass watch`` in the root folder if you want to make changes on the front end files.  

#### Silex packages

```bash
composer install
```

### Configure Apache vHost (simple)

```
<VirtualHost *:80>  
	DocumentRoot "/where/ever/your/installation/lies"  
	ServerName "choose one"  
</VirtualHost>
```

After that open your browser and go to the chosen URL of the Apache config
