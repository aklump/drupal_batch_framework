<!--
id: cron_jobs
tags: ''
-->

# Simple Cron Jobs

2. Create an operation class encapsulating your cron job.
3. (You will not be using a batch class, just a single operation.)   
4. In a _hook\_cron_ implementation, pass an instance of the operation to `CronCreateJobFromOperation`.
5. Set the maximum seconds you want to spend on the operation.

```php
function my_module_cron() {
  $foo = new FooOperation('lorem', 'ipsum');
  (new CronCreateJobFromOperation($foo))
    ->setMaxTime(30)
    ->do();
}
```

* See also [Queue Cron Jobs](@queue_cron_jobs)
