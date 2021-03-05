<?php

return [

    /**
     * You can disable Morphs from running by adding the
     * appropriate key in your .env file. This may be
     * useful for local development or testing.
     */
    'enabled' => env("RUN_MORPHS", true),

    /**
     * Define any paths that contain Morph classes.
     * We'll load them in during migrations and
     * run relevant Morphs during the process.
     */
    'paths' => [
        database_path('morphs'),
    ],

];
