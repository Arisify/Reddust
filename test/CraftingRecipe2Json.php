<?php
declare(strict_types=1);

/** use @link https://github.com/dktapps-pm-pl/Scripter to run this script */

use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Server;

class CraftingRecipe2Json{

    /** @var string */
    private string $saveFolder;

    /** @var string */
    private string $file;

    /** @var array|mixed */
    private mixed $recipes;

    private Closure $itemSerializerFunc;

    /**
     * @throws JsonException
     */
    public function __construct(){
        $scripter = Server::getInstance()->getPluginManager()->getPlugin("Scripter");

        assert($scripter !== null, "Scripter is missing! Go download and install it from https://github.com/dktapps-pm-pl/Scripter");

        $this->saveFolder = $scripter->getDataFolder();
        $this->file = $this->saveFolder . "/recipes.json";
        $this->loadRecipes();
        $this->itemSerializerFunc = static fn(Item $item) : array =>  $item->jsonSerialize();
    }

    /**
     * @throws JsonException
     */
    public function loadRecipes() : void{
        if (file_exists($this->file)) {
            $this->recipes = @json_decode(file_get_contents($this->file), true, 512, JSON_THROW_ON_ERROR) ?? [];
        } else {
            $this->recipes = [];
        }
    }

    public function reflectProperty($class, string $property) {
        $reflectionClass = new ReflectionClass($class::class);
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $property = $reflectionClass->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue(clone $class);
    }

    public function registerShapedRecipe(ShapedRecipe $recipe, bool $priority = false) : void{
        $new_recipe = [
            "block" => "crafting_table",
            "shape" => $recipe->getShape(),
            "input" => array_map($this->itemSerializerFunc, $this->reflectProperty($recipe, "ingredientList")),
            "output" => array_map($this->itemSerializerFunc, $recipe->getResults()),
            "priority" => (int) $priority
        ];
        if (!in_array($new_recipe, $this->recipes, true)) {
            $this->recipes["shaped"][] = $new_recipe;
        }
    }

    public function registerShapelessRecipe(ShapelessRecipe $recipe, bool $priority = false) : void{
        $new_recipe = [
            "block" => "crafting_table",
            "input" => array_map($this->itemSerializerFunc, $recipe->getIngredientList()),
            "output" => array_map($this->itemSerializerFunc, $recipe->getResults()),
            "priority" => (int) $priority
        ];
        if (!in_array($new_recipe, $this->recipes, true)) {
            $this->recipes["shapeless"][] = $new_recipe;
        }
    }

    public function registerSmeltingRecipe(FurnaceRecipe $recipe, $furnaceType = ""): bool{
        if ($furnaceType == "") return false;
        $new_recipe = [
            "block" => $furnaceType,
            "input" => $recipe->getInput()->jsonSerialize(),
            "output" => $recipe->getResult()->jsonSerialize()
        ];

        if (!in_array($new_recipe, $this->recipes, true)) {
            $this->recipes["shapeless"][] = $new_recipe;
        }
        return true;
    }

    public function registerStoneCutterRecipe(): void{
        // Todo
    }

    /**
     * @throws JsonException
     */
    public function saveRecipes(bool $multiline = true) : void{
        if ($multiline) file_put_contents($this->file, json_encode($this->recipes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        else file_put_contents($this->file, json_encode($this->recipes, JSON_THROW_ON_ERROR));
        echo("File exported at: $this->file");
    }
}

if (in_array(CraftingRecipe2Json::class, get_declared_classes())) {
    $class = CraftingRecipe2Json();
} else {
    $class = new CraftingRecipe2Json();
}

$class->registerShapedRecipe(new ShapedRecipe(
    [
        'A A',
        'ABA',
        ' A '
    ],
    ['A' => ItemFactory::getInstance()->get(ItemIds::IRON_INGOT), 'B' => VanillaBlocks::CHEST()->asItem()],
    [VanillaBlocks::HOPPER()->asItem()]
));

try {
    $class->saveRecipes();
} catch (JsonException $e) {
}
