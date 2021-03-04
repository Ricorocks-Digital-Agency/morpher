# Morpher

Morpher is a small Laravel package that provides a unified pattern of transforming data between database migrations.
It allows you to keep your migration logic clean and terse and move responsibility for data manipulation to a more
appropriate location.

## Installation

```bash
composer require ricorocks-digital-agency/morpher
```

You'll just need out config file to get started:

```bash
php artisan vendor:publish --tag=morpher
```

## Create your first Morph

Let's set up an example scenario. Your users table has a single `name` column, but you now need to separate it out into
`first_name` and `last_name` columns. Your application has been live for a little while, so there is going to be a need
to perform a data transformation.

You start by creating a migration:

```bash
php artisan make:migration split_names_on_users_table
```

That migration's `up` method might look something like this:

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('name');
        $table->addColumn('first_name')->nullable();
        $table->addColumn('last_name')->nullable();
    });
}
```

So, how do we go about taking all of the existing names, preparing the data, and inserting it into our new table?

Let's start by creating out first Morph:

```bash
php artisan make:morph SplitUserNames
```

This will create a new class in `app/Morphs` called `SplitUserNames`. Our next step is to link our migration to our
new Morph. In the `morpher.php` config file, you can add the link like so:

```php
'morphs' => [
    SplitNamesOnUsersTable::class => SplitUserNames::class
]
```

The key is the class name of the migration, and the value is a Morph class name or array of Morph class names.

Our next task is to describe our Morph. In the `app/Morphs/SplitUserNames` class, we need to do the following:

1. Retrieve the current names prior to the migration being run.
2. Split the names into first and last names.
3. Insert the names after the migration has finished.

To accomplish this, our `Morph` might look as follows:

```php
class SplitUserNames extends Morph
{
    protected $newNames;
    
    public function prepare()
    {
        // Get all of the names along with their ID
        $names = DB::table('users')->select(['id', 'name'])->get();
        
        // Set a class property with the mapped version of the names
        $this->newNames = $names->map(function($data) {
            $nameParts = $this->splitName($data->name);
            return ['id' => $data->id, 'first_name' => $nameParts[0], 'last_name' => $nameParts[1]];
        });
    }
    
    protected function splitName($name)
    {
        // ...return some splitting logic here
    }

    public function run()
    {
        // Now we run the database query based on our transformed data
        DB::table('users')->upsert($this->newNames->toArray(), 'id');
    }
}
```

Because we linked this `Morph` to our migration in the config file, it will be run automatically for us when we 
execute `php artisan migrate`.

## Lifecycle

It helps to understand the lifecycle that a `Morph` goes through in order to make full use it.

When a `Morph` is linked to a migration, and that migration's `up` method is run (usually from migrating the database),
the following happens (in order):

1. The `prepare` method will be called on the `Morph` class. You can do anything you need to prepare data here.
2. The migration will run.
3. The `canRun` method will be called on the `Morph` class. Returning false in this method will stop the process here.
4. The `run` method will be called on the `Morph` class. This is where you should perform your data transformations.

## Notes and Considerations

* Its important to remember that this package isn't magic. If you do something stupid to the data in your database, there
is no going back. So back up your data before migrating.
* You can override the `canRun` method to stop a faulty data set ruining your database. Perform any checks you want in this
method, and just return a boolean to tell us if we should go ahead.
