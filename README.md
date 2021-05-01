# Morpher

Morpher is a small Laravel package that provides a unified pattern of transforming data between database migrations.
It allows you to keep your migration logic clean and terse and move responsibility for data manipulation to a more
appropriate location.

## Installation

```bash
composer require ricorocks-digital-agency/morpher
```

It's not required, but you might want to publish the config file:

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

This will create a new class in `database/morphs` called `SplitUserNames`. Our next step is to link our migration to our
new Morph. We can do this using the `$migration` property in the morph class:

```php
protected static $migration = SplitNamesOnUsersTable::class;
```

If you need more complex logic, you can instead override the `migration` method and return a migration class name that way.

Our next task is to describe our Morph. In the `app/Morphs/SplitUserNames` class, we need to do the following:

1. Retrieve the current names prior to the migration being run.
2. Split the names into first and last names.
3. Insert the names after the migration has finished.

To accomplish this, our `Morph` might look as follows:

```php
class SplitUserNames extends Morph
{
    protected static $migration = SplitNamesOnUsersTable::class;
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

Now, when we run `php artisan migrate`, this Morph will run automatically.

## Lifecycle

It helps to understand the lifecycle that a `Morph` goes through in order to make full use it.

When a `Morph` is linked to a migration, and that migration's `up` method is run (usually from migrating the database),
the following happens (in order):

1. The `prepare` method will be called on the `Morph` class. You can do anything you need to prepare data here.
2. The migration will run.
3. The `canRun` method will be called on the `Morph` class. Returning false in this method will stop the process here.
4. The `run` method will be called on the `Morph` class. This is where you should perform your data transformations.

## Testing Morphs

One of the biggest challenges presented by data morphing is writing feature tests. It becomes very tricky to insert
data to test on prior to the morph taking place. And yet, automated tests are so important when the code you're running
will be modifying real data. Morpher makes the process of testing data a breeze so that you no longer have to compromise.

To get started, we recommend creating a separate test case (or more than one test case) per Morph you'd like to write
tests for. Add the `TestsMorphs` trait to that test class, and add the `supportMorphs` call to end of the `setUp` 
method.

```php
use RicorocksDigitalAgency\Morpher\Support\TestsMorphs;

class UserMorphTest extends TestCase {

    use TestsMorphs;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->supportMorphs();
    }

}
```

> :warning: The `TestsMorphs` trait conflicts with other database traits, such as `RefreshDatabase` or `DatabaseTransactions`.
> As such, ensure that your morph test cases are isolated (in separate test classes) from other tests in your suite.

With that done, you can get to work writing your tests! In order to do this, we provide a robust inspection API to 
facilitate Morph tests.

```php
use RicorocksDigitalAgency\Morpher\Facades\Morpher;

class UserMorphTest extends TestCase {

    // ...After setup
    
    public function test_it_translates_the_user_names_correctly() {
        Morpher::test(UserMorph::class)
            ->beforeMigrating(function() {
                /**
                 * We use the `beforeMigrating` hook to allow for "old"
                 * data creation. In our user names example, we'll
                 * create users with combined forename and surname.  
                 */
                 DB::table('users')->insert([['name' => 'Joe Bloggs'], ['name' => 'Luke Downing']]);
            })
            ->before(function() {
                /**
                 * We use the `before` hook to perform any expectations 
                 * after the migration has run but before the Morph
                 * has been executed.
                 */
                 $this->assertCount(2, User::all());
            })
            ->after(function() {
                /**
                 * We use the `after` hook to perform any expectations 
                 * after the morph has finished running. For example,
                 * we would expect data to have been transformed. 
                 */
                 [$joe, $luke] = User::all();
                 
                 $this->assertEquals("Joe", $joe->forename);
                 $this->assertEquals("Bloggs", $joe->surname);
                 
                 $this->assertEquals("Luke", $luke->forename);
                 $this->assertEquals("Downing", $luke->surname);
            });
    }

}
```

## Disabling Morphs

It may be helpful, particularly in local development where you destroy and rebuild the database regularly, to disable
Morphs from running. To do this, add the following to your environment file:

```dotenv
RUN_MORPHS=false
```

## Notes and Considerations

* Everything in the `run` method is encapsulated in a database transaction. This means that if there is an exception 
  whilst running your morph, no data changes will be persisted.
* Its important to remember that this package isn't magic. If you do something stupid to the data in your database, there
  is no going back. **Back up your data before migrating.**
* You can override the `canRun` method to stop a faulty data set ruining your database. Perform any checks you want in this
  method, and just return a boolean to tell us if we should go ahead.
