
## В командной строке запуск

```php parser.php [--parser=example.org] [--report=example.org] [--help]```

##Опции:

--parser=url              

запускает парсер, принимает обязательный параметр url (как с протоколом, так и без).

--report=url 

выводит в консоль результаты анализа для домена, принимает обязательный параметр domain (как с протоколом, так и без)

--help      

выводит список команд с пояснениями

###### **Пример**:
```php parser.php --parse=https://netpeak.net/ru/blog/```

```php parser.php --report=netpeak.net```
