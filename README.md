# Color Palettes

## Installation

To use this platform, you need:

- [NodeJS](https://nodejs.org/en/) (0.10 or greater)
- [Git](https://git-scm.com/)
- [Composer](https://getcomposer.org)

### Install needed components

#### Foundation CLI

Install the Foundation CLI with this command:

```bash
npm install foundation-cli --global
```

#### NPM Modules

```bash
(sudo for mac) npm install
```

#### Bower
```bash
(sudo for mac) npm install -g bower
```

#### Foundation SASS files

```bash
bower install
```

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