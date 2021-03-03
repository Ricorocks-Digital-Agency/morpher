<?php

return [

    /**
     * Define any paths that contain Morph classes.
     * We'll load them in during migrations and
     * run relevant Morphs during the process.
     */
    'paths' => [
        database_path('morphs'),
    ],

];
