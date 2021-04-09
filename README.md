# BambiPostgresTextSearchBundle

Symfony bundle that integrates [PostgreSQL](https://www.postgresql.org/)
full-text search functionality in [Api-Platform](https://api-platform.com/).

This bundle has been developed for and tested with PostgreSQL 12.

## Work in Progress!
__This bundle is still in a very early stage of development.__

---

### Installation
`composer require bambi/PostgresTextSearchBundle`

### Basic Usage
```php
namespace App\Entity;

class Book
{
    private string $name;
    private string $isbn;
    
    ...
}
```
```xml
<services>
    <service id="app.book.text_search_match_filter"
             parent="bambi_postgres_text_search.filter.text_search_match_filter">
        
        <!-- Enable properties for text search -->
        <argument type="collection">
            <argument key="name" />
            <argument key="isbn"
        </argument>
        
        <!-- Optionally you can configure the parameter name (default="text_search") for the for the API
        and the config string (default="'english'") used by Postgres for text search. -->
        <argument key="$textSearchParameterName" type="string">postgres_text_search</argument>
        <argument key="$postgresTsConfigString" type="string">'german'</argument>

        <tag name="api_platform.filter" />
    </service>
</services>
```
Bear in mind that the value for _postgresTsConfigString_ is directly passed on to your Postgres Database. Don't forget Quotes if you want to pass a literal value (_'german'_ instead of _german_)
```xml
<resource class="App\Entity\Book">
    <collectionOperations>
        <collectionOperation name="get">
            <attribute name="method">GET</attribute>
            ...
            <attribute name="filters">
                <attribute>app.book.text_search_match_filter</attribute>
            </attribute>
        </collectionOperation>
    </collectionOperations>
</resource>
```
You can access this filter via:
`GET /api/books?postgres_text_search[name]=QUERY_STRING&postgres_text_search[isbn]=QUERY_STRING`