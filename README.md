# ServerTransfer
PocketMine-MP Server Transfer for Human Query.

```php
<?php

class Main extends PluginBase{

    public function onEnable(): void{
        $this->getLogger()->info("Server Address: " . gethostbyname("test.kro.kr"));
    }
}
?>
```
