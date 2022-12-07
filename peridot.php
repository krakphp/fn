<?php

use Krak\Peridocs;

return function($emitter) {
    Peridocs\bootstrap($emitter, function() {
        return new Peridocs\DocsContext(null, [
            'showLinks' => true,
            'nsPrefix' => 'Krak\\Fun\\',
            'numTableRows' => 10,
        ]);
    });
};

