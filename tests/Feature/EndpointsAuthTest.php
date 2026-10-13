<?php

it('rejects guests', function () {
    $this->getJson('/nova-vendor/nova-global-filters/filters/date_range')->assertUnauthorized();
    $this->postJson('/nova-vendor/nova-global-filters/filters/date_range', [])->assertUnauthorized();
});
