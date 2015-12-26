<?php

namespace Makotokw\Garapon;

class TvTest extends TestCase
{
    /**
     * @var Tv
     */
    protected static $tv;

    public static function setUpBeforeClass()
    {
        self::$tv =  new Tv($_ENV['GARAPON_TV_ADDRESS'], $_ENV['GARAPON_TV_PORT']);
        self::$tv->setDevId($_ENV['GARAPON_DEV_ID']);
    }

    public function testAuth()
    {
        $tv = self::$tv;
        $this->assertEquals(200, $tv->login($_ENV['GARAPON_LOGINID'], 'foo'), 'login error');
        $this->assertEquals(1, $tv->login($_ENV['GARAPON_LOGINID'], $_ENV['GARAPON_MD5PSWD']), 'login success');
        $this->assertNotEmpty($tv->getSessionId(), 'get gtvsession');
        $this->assertEquals(1, $tv->logout(), 'logout success');

        $this->assertEquals(1, $tv->login($_ENV['GARAPON_LOGINID'], $_ENV['GARAPON_MD5PSWD']), 'login success');
    }

    public function testSearch()
    {
        $tv = self::$tv;
        $data = $tv->search(
            array(
                's' => 'e',
                'genre0' => 5,
                'genre1' => 2,
                'sort'   => 'sta',
                'video'  => 'all',

            )
        );
        $this->assertArrayHasKey('status', $data, 'no status');
        $this->assertEquals(1, $data['status'], 'status is not successful');
        $this->assertArrayHasKey('hit', $data, 'no hit');
        $this->assertTrue($data['hit'] > 0, 'no hit');
        $this->assertArrayHasKey('program', $data, 'no program');
        $this->assertEquals(20, count($data['program']), 'no hit');

        return $data['program'];
    }

    /**
     * @depends testSearch
     * @param array $programs
     * @return array
     */
    public function testSearchOne(array $programs)
    {
        if (empty($programs)) {
            return array();
        }

        $tv = self::$tv;

        $randomProgram = $programs[rand(0, count($programs)-1)];

        $data = $tv->search(array('gtvid' => $randomProgram['gtvid']));
        $this->assertArrayHasKey('status', $data, 'no status');
        $this->assertEquals(1, $data['status'], 'status is not successful');
        $this->assertArrayHasKey('hit', $data, 'no hit');
        $this->assertTrue($data['hit'] > 0, 'no hit');
        $this->assertArrayHasKey('program', $data, 'no program');
        $this->assertEquals(1, count($data['program']), 'no hit');

        return $data['program'];
    }

//    /**
//     * @depends testSearchOne
//     */
//    public function testFavorite(array $programs)
//    {
//        $this->markTestSkipped();
//
//        $tv = self::$tv;
//        $gtvid = $programs[0]['gtvid'];
//        $this->assertEquals(1, $tv->favorite($gtvid, 50), 'add fav');
//        $this->assertEquals(1, $tv->favorite($gtvid, 0), 'remove fav');
//        $this->assertEquals(0, $tv->favorite($gtvid, 0), 'remove nothing fav');
//    }
//

    public function testChannel()
    {
        $tv = self::$tv;
        $data = $tv->channel();
        $this->assertArrayHasKey('status', $data, 'no status');
        $this->assertEquals(1, $data['status'], 'status is not successful');
        $this->assertArrayHasKey('ch_list', $data, 'no ch_list');
        $this->assertTrue(count($data['ch_list']) > 0, 'ch_list is empty');
    }
}
