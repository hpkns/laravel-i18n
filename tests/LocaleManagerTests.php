<?php

use \Mockery as m;
use Kowali\I18n\LocaleManager as Manager;

class LocaleManagerTests extends PHPUNIT_Framework_TestCase {


    public function tearDown()
    {
        m::close();
    }

    public function getMocks()
    {
        return [
            'request'   => m::mock('\Illuminate\Http\Request'),
            'app'       => m::mock('\Illuminate\Foundation\Application'),
            'key'       => 'locale',
        ];
    }

    public function testIsInstanciable()
    {
        extract($this->getMocks());

        $this->assertInstanceOf('\Kowali\I18n\LocaleManager', new Manager([], $key, $request, $app));
    }

    public function testGuessMethod()
    {
        extract($this->getMocks());
        $locales = ['fr'];
        $manager = m::mock('\Kowali\I18n\LocaleManager[pickFromAccepted, getCookie, getHeaderAcceptedLocales, isAvailable]', [$locales, $key, $request, $app]);

        $manager->shouldReceive('getCookie')
            ->with('locale')
            ->once()
            ->andReturn(null);

        $manager->shouldReceive('pickFromAccepted')
            ->with($locales, $locales)
            ->once()
            ->andReturn($locales[0]);

        $manager->shouldReceive('getHeaderAcceptedLocales')
            ->once()
            ->andReturn($locales);

        $this->assertEquals($locales[0], $manager->guess());

        $manager->shouldReceive('getCookie')
            ->with('locale')
            ->once()
            ->andReturn('fr');

        $manager->shouldReceive('isAvailable')
            ->with('fr')
            ->once()
            ->andReturn(true);

        $this->assertEquals($locales[0], $manager->guess());
    }

    public function testGetHeaderAcceptedLocalesMethod()
    {
        extract($this->getMocks());
        $manager = new Manager([], $key, $request, $app);
        $accepted = 'some value';

        $request->shouldReceive('server')
            ->with('HTTP_ACCEPT_LANGUAGE')
            ->once()
            ->andReturn($accepted);

        $this->assertEquals($manager->getHeaderAcceptedLocales(), (array)$accepted);
    }

    public function testIsAvailableMethod()
    {
        extract($this->getMocks());
        $manager = new Manager(['fr','en'], $key, $request, $app);

        $this->assertTrue($manager->isAvailable('fr'));
        $this->assertTrue($manager->isAvailable('en'));
        $this->assertfalse($manager->isAvailable('ru'));
    }

    public function testSetMethod()
    {
        extract($this->getMocks());
        $manager = new Manager(['en'], $key, $request, $app);

        $app->shouldReceive('setLocale')
            ->with('en')
            ->once();

        $this->assertEquals($manager->set('en'), 'en');
        $this->assertFalse($manager->set('ru'));
    }

    public function testDetectFromHeaderMethod()
    {
        extract($this->getMocks());
        $manager = new Manager(['en'], $key, $request, $app);

        $available = ['fr','en','nl'];

        $accepted = explode(',', 'fr,en;q=0.9,de;q=0.5');
        $this->assertEquals($manager->pickFromAccepted($available, $accepted), 'fr');

        $accepted = explode(',', 'po;q=0.9,nl;q=0.5');
        $this->assertEquals($manager->pickFromAccepted($available, $accepted), 'nl');

        $accepted = explode(',', 'en;q=0.9,de;q=0.5');
        $this->assertEquals($manager->pickFromAccepted($available, $accepted), 'en');

        $accepted = explode(',', 'ru,po;=0.9,da;=0.2');
        $this->assertEquals($manager->pickFromAccepted($available, $accepted), 'fr');
    }

    public function testSetGuessedMethod()
    {
        extract($this->getMocks());
        $locale = 'fr';
        $locales = [$locale];
        $manager = m::mock('\Kowali\I18n\LocaleManager[guess]', [$locales, $key, $request, $app]);

        $manager->shouldReceive('guess')
            ->once()
            ->andReturn($locale);

        $app->shouldReceive('setLocale')
            ->with($locale)
            ->once();

        $this->assertEquals($manager->setGuessed(), 'fr');
    }
}
