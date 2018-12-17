<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\Key;

class ApiV4Test extends TestCase
{

    protected $params;
    protected $swagger;
    protected $schemas;
    protected $key;

    /**Api_V2_Test constructor
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->key    = Key::where('name', 'test-key')->first()->key;
        $this->params = ['v' => 4, 'key' => $this->key, 'pretty'];

        // Fetch the Swagger Docs for Structure Validation
        $arrContextOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        $swagger_url       = base_path('resources/assets/js/swagger_v4.json');
        $this->swagger     = json_decode(file_get_contents($swagger_url, false, stream_context_create($arrContextOptions)), true);
        ini_set('memory_limit', '1264M');
    }

    public function getSchemaKeys($schema)
    {
        if (isset($this->swagger['components']['schemas']['items'])) {
            return array_keys($this->schemas[$schema]['items']['properties']);
        }
        return array_keys($this->schemas[$schema]['properties']);
    }
}