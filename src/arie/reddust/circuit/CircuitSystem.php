<?php
declare(strict_types=1);
namespace arie\reddust\circuit;

use arie\reddust\Loader;
use pocketmine\math\Facing;
use pocketmine\Server;

use pocketmine\event\Listener;
use pocketmine\world\Position;
use pocketmine\world\World;

use ArrayIterator;
use LogicException;

use muqsit\asynciterator\AsyncIterator;
use muqsit\asynciterator\handler\AsyncForeachHandler;
use muqsit\asynciterator\handler\AsyncForeachResult;

final class CircuitSystem implements Listener{
    private static CircuitSceneGraph $graph;
    /** @var AsyncIterator */
    private $async_iterator;

    /** @var AsyncForeachHandler<int, CircuitSystem>|null */
    private $ticker;

    /* @var bool */
    private bool $hasBeenEvaluated;

    private bool $lockGraph = true;

	/** @var CircuitSystem */
	private static $instance;

    private CircuitSceneGraph $circuit_graph;

	public static function getInstance() : self{
        return self::$instance;
    }

    public function __construct(
        private Loader $plugin
    ) {
        self::$instance = $this;
    }

    /*
     * Interface called to the outside (Dimension object)
     */
    public function update(BlockSource $source) : void{
        $this->circuit_graph->update();
        $this->hasBeenEvaluated = false;
    }

    /**
     * @return bool
     */
    public function isEvaluated(): bool{
        return $this->hasBeenEvaluated;
    }

    public function evaluate(BlockSource $source) :void{
        $this->shouldEvaluate($source);
        $this->cacheValues();
        $this->evaluateComponents(true);
        $this->evaluateComponents(false);
        $this->checkLocks();
        $this->hasBeenEvaluated = true;
    }

    public function shouldEvaluate(BlockSource $source) {
        $components = $this->circuit_graph->getComponents_FastLookupByChunkPos();
        foreach ($components as $component) {
           $shouldEvaluate = $source->sreChunksFullyLoaded($component, 32);
            if ($component->shouldEvaluate()) {
                $component->cacheValues($this, $component->position);
            }
        }
    }

    /*
     * The process of calculating new values of all redstone originals in the circuit and buffering them
     * Not all originals will cacheValue, it depends on their specific implementation
     */

    public function cacheValues() : void{
        $comMap = $this->circuit_graph->getComponents_FastIterationAcrossActive();
        foreach ($comMap as $component) {
            if ($component->shouldEvaluate()) {
                $component->cacheValues($this, $component->position);
            }
        }
    }

    /*
     * Check lock: Only valid for redstone looping
    */
    public function checkLocks() {
        $comMap = $this->circuit_graph->getComponents_FastIterationAcrossActive();
        foreach ($comMap as $component) {
            if ($component->shouldEvaluate()) {
                $component->checkLock($this, $component->position);
            }
        }
    }

    /*
     * The process of updating the actual signal value
     * Or the process of synchronizing the new value and the old value
     * Separately for each producer and non-producer of redstone carvings
     */
    public function evaluateComponents(bool $only_producers = false) : void{
        $comMap = $this->circuit_graph->getComponents_FastIterationAcrossActive();
        foreach ($comMap as $component) {
            $typeId = $component->getBaseType();
            if ($component->shouldEvaluate()) {
                if (($typeId == ComponentTypeID::CSPC || $typeId == ComponentTypeID::CSCA) == $only_producers) {
                    $component->evaluate($this, $component->position);
                    $component->setNeedUpdate(true);
                }
            }
        }
    }

    public function getDirection(Position $position) {
        $component = $this->circuit_graph->getBaseComponent($position);
        return $component instanceof Component ? $component->getFacing() : Facing::NORTH;
    }

    public function getStrength(Position $position) : int{
        $component = $this->circuit_graph->getBaseComponent($position);
        return $component instanceof Component ? $component->getStrength() : -1;
    }

    public function hasDirectPower(Position $position) : bool{
        $component = $this->circuit_graph->getBaseComponent($position);
        return $component instanceof Component && $component->hasDirectPower(); // !== null - not instanceof Component but because of how php deal with this so yeah, same stuff
    }

    public function invalidatePosition(Position $position) {
        //Todo
    }

    public function isAvailableAt(Position $position) {
        return $this->circuit_graph->getBaseComponent($position) !== null;
    }

    public function preSetupPoweredBlocks(Position $position) {
        //Todo
    }

    public function removeComponents(Position $position) {
        //Remove the circuit diagram if it is not locked
        if ($this->lockGraph) {
            $component = $this->circuit_graph->getBaseComponent($position);
            $this->circuit_graph->remove($position, $component);
        }
    }

    public function setStrength(Position $position, int $strength = 0) {
        $component = $this->circuit_graph->getBaseComponent($position);
        if ($component instanceof Component) $component->setStrength($strength);
    }

    public function updateDependencies(BlockSource $source) {
        $this->circuit_graph->update($source);
        $this->hasBeenEvaluated = false;
    }

    public function createComponent(Position $position, int $facing, Component $new_component) {
        $component = $new_component->get();

        $component->setDirection($facing);

        if ($this->lockGraph) {
            $new_component->reset($component);
            return null;
        }
        $this->circuit_graph->add($position, $new_component);
        return $this->circuit_graph->getFromPendingAdd($position);
    }

    public function tick() {

        if($this->ticker !== null){
            throw new LogicException("Tried scheduling multiple item entity tickers");
        }

        if($tick_rate > 0){
            $this->ticker = $this->async_iterator->forEach(new ArrayIterator($this->entities), $per_tick, $tick_rate)->as(static function(int $id,  CircuitList $list) : AsyncForeachResult{
                $list->update();
                return AsyncForeachResult::CONTINUE();
            })->onCompletion(function() : void{
                $this->ticker = null;
                $this->tick();
            });
            return true;
        }

        return false;
    }
}