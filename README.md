Akeneo OpenAI Translator
=====================================


Extension for Akeneo adding mass translation options for attributes.

### Installation

Install composer dependency

```shell
composer require macopedia/akeneo-openai-translator
```

define Open AI Key in `.env` file:

```dotenv
OPEN_AI_KEY=yourapikey
```

add new job instance

```shell
bin/console akeneo:batch:create-job internal update_product_translations mass_edit update_product_translations '{}' 'Translate product'
```


Functionality
-------------------------
With our extension you can mass translate one attribute assigned to one channel to multiple attributes in multiple languages across different channels.

The highest quality of translation is ensured by artificial intelligence.

Extension uses [Open AI API](https://openai.com/product) - ChatGPT v3.5
### Requirements:

* Akeneo PIM >= 6.x