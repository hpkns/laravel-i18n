<?php

use Mockery as m;
use Hpkns\I18n\LocaleManager;
use Illuminate\Http\Request;

class LocaleManagerTest extends PHPUnit_Framework_TestCase
{

    protected $default = [['en', 'de'], 'locale', true];

    public function tearDown()
    {
        m::close();
    }

    public function testIsInstantiable()
    {
        $instance = new LocaleManager();

        $this->assertInstanceOf(LocaleManager::class, $instance);
    }

    public function testGuessFunction()
    {
        $r = m::mock(Request::class);
        $m = m::mock(LocaleManager::class.'[pickFromAccepted]', [['en', 'de'], 'locale', $r]);

        // Return a valid value
        $r->shouldReceive('cookie')->once()->andReturn('en');
        $this->assertEquals('en', $m->guess());
    }

    /**
     * @expectedException \Hpkns\I18n\Exceptions\NoAvailableLocalesSetException
     */
    public function testPickFromAcceptedFunction()
    {
        $r = m::mock(Request::class);
        $m = m::mock(LocaleManager::class.'[getHeaderAcceptedLocales]', [['ru', 'en', 'de'], 'locale', $r]);
        $r->shouldReceive('cookie')->andReturn(null);
        $m->shouldReceive('getHeaderAcceptedLocales')->withNoArgs()->times(3)->andReturn(
            explode(',', 'pt,en;q=0.8'),
            explode(',', 'pt'),
            explode(',', 'pt')
        );

        $this->assertEquals('en', $m->guess());
        $this->assertEquals('ru', $m->guess(true));
        $this->assertNull($m->guess());

        $m = m::mock(LocaleManager::class.'[getHeaderAcceptedLocales]', [['ru', 'en', 'de'], 'locale', $r]);
        $m->pickFromAccepted([], []);
    }

    /**
     * @expectedException \Hpkns\I18n\Exceptions\EmptyAcceptHeader
     */
    public function testGetHeaderAcceptedLocalesFunction()
    {
        $r = m::mock(Request::class);
        $m = m::mock(LocaleManager::class.'[]', [[], 'locale', $r]);
        $r->shouldReceive('server')->twice()->with('HTTP_ACCEPT_LANGUAGE')->andReturn(
            'en,fr;=i',
            null        // To cause an exception
        );

        $m->getHeaderAcceptedLocales();
        $m->getHeaderAcceptedLocales(); // 2: Throws an EmptyAcceptHeader exception
    }

    public function testAvailableFunction()
    {
        $a = ['fr', 'en'];
        $m = new LocaleManager($a, 'locale', m::mock(Request::class));

        $this->assertEquals($a, $m->available());
    }

    public function testSetFunction()
    {
        $m = m::mock(LocaleManager::class.'[saveLocale]', [['en', 'de'], 'locale', true]);
        $a = m::mock('not_a_real_class');

        $this->assertFalse($m->set('ru'));

        $a->shouldReceive('setLocale')->once()->with('en');
        $m->shouldReceive('saveLocale')->once()->with('en');
        App::setInstance($a);
        $m->set('en', true);
    }

    public function testSaveLocaleFunction()
    {
        $m = m::mock(LocaleManager::class.'[]', [['en', 'de'], 'locale', true]);
        $a = m::mock('not_a_real_class');

        $this->assertFalse($m->saveLocale('ru'));

        App::setInstance($a);
        $a->shouldReceive('queue')->once()->with('locale','en', 144000);
        $this->assertEquals('en', $m->saveLocale('en'));

    }

    public function testGetFunction()
    {
        $a = m::mock('not_a_real_class');
        $a->shouldReceive('getLocale')->once()->andReturn('en');
        $m = m::mock(LocaleManager::class.'[]', $this->default);

        App::setInstance($a);

        $m->get();
    }


    public function testSetGuessedFunction()
    {
        $m = m::mock(LocaleManager::class.'[set,guess]', $this->default);
        $m->shouldReceive('set', 'guess')->once()->andReturn('en', 'en');

        $this->assertEquals('en', $m->setGuessed(true));
    }
}
