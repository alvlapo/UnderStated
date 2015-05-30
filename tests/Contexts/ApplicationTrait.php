<?php namespace Contexts;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use FSM\Machine;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;

trait ApplicationTrait {

    /**
     * @var Machine
     */
    protected $fsm;

    /**
     * @var Container
     */
    protected static $app;

    /**
     * @beforeSuite
     */
    public static function setUpSuite()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        static::$app = new Container();

        static::$app->bind(Container::class, function($app)
        {
            return $app;
        });

        $events = new EventServiceProvider(static::$app);
        $events->register();
    }

    /**
     * @Given /^I have a "([^"]*)" instance$/
     */
    public function iHaveAInstance($fsm)
    {
        $this->fsm = $this->createInstance($fsm);
    }

    /**
     * @Given /^I run handle (.*)$/
     */
    public function iRunHandle($handle)
    {
        $this->fsm->handle($handle);
    }

    /**
     * @Then /^The state should be (.*)$/
     */
    public function theStateShouldBe($state)
    {
        $fsmState = $this->fsm->getCurrentStateId();

        if ($fsmState !== $state)
        {
            throw new \Exception("FSM state is {$fsmState}. Not {$state}.");
        }
    }

    private function createInstance($fsm)
    {
        $class = '\\'.ltrim(str_replace('/', '\\', $fsm), '\\');

        return static::$app->make($class);
    }

    /**
     * @Then /^Possible transitions should be$/
     */
    public function possibleTransitionsShouldBe(TableNode $table)
    {
        $states = $table->getRow(0);

        $transitions = $this->fsm->getPossibleTransitions();

        foreach ($states as $state)
        {
            if ( ! in_array($state, $transitions) )
            {
                throw new \Exception("{$state} was not in the possible transitions list.");
            }
        }
    }

    /**
     * @When /^I transition to (.*)$/
     */
    public function iTransitionTo($transition)
    {
        $this->fsm->transition($transition);
    }

    /**
     * @Given /^Initial state is (.*)$/
     */
    public function initialStateIs($initial_state)
    {
        $this->fsm->initialise($initial_state);
    }
}