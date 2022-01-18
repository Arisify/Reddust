<?php
declare(strict_types=1);

use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Server;

class CraftingRecipe2Json{

    /** @var array|mixed */
    private mixed $recipes;

    private ?Closure $itemSerializerFunc = null;

    public function __construct(
        private string $filePath
    ){
        if (file_exists($this->filePath) && pathinfo($filePath, PATHINFO_EXTENSION) !== "json") {
            Server::getInstance()->getLogger()->notice("File input is not a supported json format file!");
        }
        try {
            $contents = @file_get_contents($filePath);
            if (!$contents) $contents = "{}";
            $recipes = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Server::getInstance()->getLogger()->error("An issue occurred while trying to read data. Input data will be returned as an empty array!");
            print($e->getMessage());
            $recipes = [];
        }
        $this->recipes = $recipes;
        $this->itemSerializerFunc = static fn(Item $item) : array =>  $item->jsonSerialize();
    }


    /**
     * @throws ReflectionException
     */
    public function registerShapedRecipe(ShapedRecipe $recipe, bool $priority = false) : void{
        $new_recipe = [
            "block" => "crafting_table",
            "shape" => $recipe->getShape(),
            "input" => array_map($this->itemSerializerFunc, $this->reflectProperty($recipe, "ingredientList")),
            "output" => array_map($this->itemSerializerFunc, $recipe->getResults()),
            "priority" => (int) $priority
        ];
        if (!in_array($new_recipe, $this->recipes["shaped"], true)) {
            $this->recipes["shaped"][] = $new_recipe;
        } else {
            $this->recipes["shaped"][array_search($new_recipe, $this->recipes["shaped"], true)] = $new_recipe;
        }
    }

    public function registerShapelessRecipe(ShapelessRecipe $recipe, bool $priority = false) : void{
        $new_recipe = [
            "block" => "crafting_table",
            "input" => array_map($this->itemSerializerFunc, $recipe->getIngredientList()),
            "output" => array_map($this->itemSerializerFunc, $recipe->getResults()),
            "priority" => (int) $priority
        ];
        if (!in_array($new_recipe, $this->recipes["shapeless"], true)) {
            $this->recipes["shapeless"][] = $new_recipe;
        } else {
            $this->recipes["shapeless"][array_search($new_recipe, $this->recipes["shapeless"], true)] = $new_recipe;
        }
    }

    public function registerSmeltingRecipe(FurnaceRecipe $recipe, $furnaceType = ""): bool{
        if ($furnaceType === "") {
            return false;
        }
        $new_recipe = [
            "block" => $furnaceType,
            "input" => $recipe->getInput()->jsonSerialize(),
            "output" => $recipe->getResult()->jsonSerialize()
        ];

        if (!in_array($new_recipe, $this->recipes["smelting"] ?? [], true){
        $this->recipes["smelting"][] = $new_recipe;
    } else {
            $this->recipes["smelting"][array_search($new_recipe, $this->recipes["smelting"])] = $new_recipe;
        }
        return true;
    }

    public function registerStoneCutterRecipe(): void{
        // Todo
    }

    public function saveRecipes(string $filePath = "", bool $multiline = true) : bool{
        if ($filePath === "") $filePath = $this->filePath;
        if (file_exists($filePath)) {
            Server::getInstance()->getLogger()->info("Overwriting current file: $filePath");
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) !== "json") {
            Server::getInstance()->getLogger()->notice("File extension is supposed to be json format file!");
        }

        try {
            file_put_contents($filePath, json_encode($this->recipes, JSON_THROW_ON_ERROR | ($multiline ? JSON_PRETTY_PRINT : 0)));
        } catch (JsonException $e) {
            Server::getInstance()->getLogger()->error("An error has occurred while attempting to process and store the data" . $e->getMessage());
            return false;
        }
        Server::getInstance()->getLogger()->info("File exported at: $this->filePath");
        return true;
    }
}

$class = new CraftingRecipe2Json(Server::getInstance()->getDataPath() . '/recipes.json');
try {
    $class->registerShapedRecipe(new ShapedRecipe(
        [
            'A A',
            'ABA',
            ' A '
        ],
        ['A' => ItemFactory::getInstance()->get(ItemIds::IRON_INGOT), 'B' => VanillaBlocks::CHEST()->asItem()],
        [VanillaBlocks::HOPPER()->asItem()]
    ));
} catch (ReflectionException $e) {
    //NOOP
}

try {
    $class->saveRecipes();
} catch (JsonException $e) {
    //NOOP
}