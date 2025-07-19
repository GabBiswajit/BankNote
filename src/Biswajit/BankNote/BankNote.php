<?php

namespace Biswajit\BankNote;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\block\ItemFrame;
use pocketmine\block\Chest;
use pocketmine\block\EnderChest;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use onebone\economyapi\EconomyAPI;

class BankNote extends PluginBase implements Listener{

    private array $cooldowns = [];
    private ?object $economyProvider = null;
    private string $economyType = "none";

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->initializeEconomy();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    private function initializeEconomy(): void {
        $pluginManager = $this->getServer()->getPluginManager();
        
        if ($pluginManager->getPlugin("BedrockEconomy") !== null) {
            $this->economyType = "BedrockEconomy";
        } elseif ($pluginManager->getPlugin("EconomyAPI") !== null) {
            $this->economyProvider = EconomyAPI::getInstance();
            $this->economyType = "EconomyAPI";
        } else {
            $this->economyType = "none";
        }
    }

    public function getCooldowns(): array {
        return $this->cooldowns;
    }
    
    public function setCooldown(string $playerName, int $time): void {
        $this->cooldowns[$playerName] = $time;
    }
    
    public function getItemFromConfig(string $itemType): Item {
        $item = StringToItemParser::getInstance()->parse($itemType);
        if ($item !== null) {
            return $item;
        }
        
        switch (strtolower($itemType)) {
            case "paper":
                return VanillaItems::PAPER();
            case "book":
                return VanillaItems::BOOK();
            case "written_book":
                return VanillaItems::WRITTEN_BOOK();
            case "name_tag":
                return VanillaItems::NAME_TAG();
            case "diamond":
                return VanillaItems::DIAMOND();
            case "emerald":
                return VanillaItems::EMERALD();
            case "gold_ingot":
                return VanillaItems::GOLD_INGOT();
            case "iron_ingot":
                return VanillaItems::IRON_INGOT();
            default:
                return VanillaItems::PAPER();
        }
    }
    
    public function getMoney(Player $player, callable $callback): void {
        switch ($this->economyType) {
            case "BedrockEconomy":
                BedrockEconomyAPI::legacy()->getPlayerBalance(
                    $player->getName(),
                    function(float $balance) use ($callback): void {
                        $callback($balance);
                    }
                );
                break;
            case "EconomyAPI":
                $money = $this->economyProvider->myMoney($player);
                $callback((float) $money);
                break;
            default:
                $callback(0.0);
                break;
        }
    }
    
    public function addMoney(Player $player, float $amount, callable $callback): void {
        switch ($this->economyType) {
            case "BedrockEconomy":
                BedrockEconomyAPI::legacy()->addToPlayerBalance(
                    $player->getName(),
                    $amount,
                    function(bool $success) use ($callback): void {
                        $callback($success);
                    }
                );
                break;
            case "EconomyAPI":
                $result = $this->economyProvider->addMoney($player, $amount);
                $callback($result === EconomyAPI::RET_SUCCESS);
                break;
            default:
                $callback(false);
                break;
        }
    }
    
    public function reduceMoney(Player $player, float $amount, callable $callback): void {
        switch ($this->economyType) {
            case "BedrockEconomy":
                BedrockEconomyAPI::legacy()->subtractFromPlayerBalance(
                    $player->getName(),
                    $amount,
                    function(bool $success) use ($callback): void {
                        $callback($success);
                    }
                );
                break;
            case "EconomyAPI":
                $result = $this->economyProvider->reduceMoney($player, $amount);
                $callback($result === EconomyAPI::RET_SUCCESS);
                break;
            default:
                $callback(false);
                break;
        }
    }
    
    public function createBankNoteItem(Player $player, float $amount, string $playerName): void {
        $config = $this->getConfig();
        $itemType = $config->getNested("banknote.item.type", "paper");
        $item = $this->getItemFromConfig($itemType);
        
        $nameFormat = $config->getNested("banknote.item.name", "§r§l§6$%amount% §aBAN§fK §aNOTE");
        $customName = str_replace("{amount}", number_format($amount, 2), $nameFormat);
        $item->setCustomName($customName);
        
        $loreTemplate = $config->getNested("banknote.item.lore", [
            "§r§7Right-click to redeem this §aBank Note",
            "§r§7Withdrawn by: §f{creator}",
            "§r§7Date: §f{date}",
            "",
            "§r§7Value: §a${amount}"
        ]);
        
        $date = date("d/m/Y H:i");
        $lore = [];
        foreach ($loreTemplate as $line) {
            $line = str_replace("{amount}", number_format($amount, 2), $line);
            $line = str_replace("{creator}", $playerName, $line);
            $line = str_replace("{date}", $date, $line);
            $lore[] = $line;
        }
        
        $item->setLore($lore);
        
        $nbt = $item->getNamedTag();
        $nbt->setFloat("BankNoteAmount", $amount);
        $nbt->setString("BankNoteCreator", $playerName);
        $nbt->setString("BankNoteDate", $date);
        $item->setNamedTag($nbt);
        
        $player->getInventory()->addItem($item);
        $player->sendMessage(TextFormat::GREEN . "You have successfully converted $" . number_format($amount, 2) . " to a bank note!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() !== "banknote") {
            return false;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game!");
            return true;
        }

        if (!$sender->hasPermission("banknote.use")) {
            $sender->sendMessage(TextFormat::RED . "You don't have permission to use banknotes!");
            return true;
        }

        if (empty($args) || (isset($args[0]) && strtolower($args[0]) === "ui")) {
            if ($this->getConfig()->getNested("ui.enabled", true)) {
                $this->openBankNoteUI($sender);
                return true;
            } else {
                $sender->sendMessage(TextFormat::RED . "UI is disabled. Usage: /banknote <amount>");
                return true;
            }
        }

        if (!is_numeric($args[0]) || (float) $args[0] <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please enter a valid positive number!");
            return true;
        }

        $amount = (float) $args[0];
        $maxAmount = $this->getConfig()->getNested("banknote.limits.maximum", 1000000);
        $minAmount = $this->getConfig()->getNested("banknote.limits.minimum", 1);

        if ($amount > $maxAmount) {
            $sender->sendMessage(TextFormat::RED . "Maximum banknote amount is $" . number_format($maxAmount));
            return true;
        }

        if ($amount < $minAmount) {
            $sender->sendMessage(TextFormat::RED . "Minimum banknote amount is $" . number_format($minAmount));
            return true;
        }

        $this->processBankNoteCreation($sender, $amount);
        return true;
    }
    
    private function openBankNoteUI(Player $player): void {
        if (!class_exists(SimpleForm::class)) {
            $this->openSimpleBankNoteUI($player);
            return;
        }
        
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            
            $quickAmounts = $this->getConfig()->getNested("ui.quick_amounts", [100, 500, 1000, 5000, 10000, 50000]);
            
            if ($data === count($quickAmounts)) {
                $this->openCustomAmountUI($player);
                return;
            }
            
            if (isset($quickAmounts[$data])) {
                $amount = (float) $quickAmounts[$data];
                $this->processBankNoteCreation($player, $amount);
            }
        });
        
        $form->setTitle($this->getConfig()->getNested("ui.title", "§l§aBankNote Manager"));
        $form->setContent("§7Select an amount to create a banknote:\n§8Your current balance will be checked automatically.");
        
        $quickAmounts = $this->getConfig()->getNested("ui.quick_amounts", [100, 500, 1000, 5000, 10000, 50000]);
        foreach ($quickAmounts as $amount) {
            $form->addButton("§6$" . number_format($amount) . "\n§7Create Banknote", 0, "textures/items/paper");
        }
        
        $form->addButton("§eCustom Amount\n§7Enter your own amount", 0, "textures/items/writable_book");
        $player->sendForm($form);
    }
    
    private function openCustomAmountUI(Player $player): void {
        if (!class_exists(CustomForm::class)) {
            $player->sendMessage(TextFormat::YELLOW . "Please use: /banknote <amount>");
            return;
        }
        
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;
            
            $amount = (float) ($data[0] ?? 0);
            if ($amount <= 0) {
                $player->sendMessage(TextFormat::RED . "Please enter a valid positive amount!");
                return;
            }
            
            $this->processBankNoteCreation($player, $amount);
        });
        
        $form->setTitle("§l§eCustom Banknote Amount");
        $maxAmount = $this->getConfig()->getNested("banknote.limits.maximum", 1000000);
        $minAmount = $this->getConfig()->getNested("banknote.limits.minimum", 1);
        
        $form->addInput("Enter amount (Min: $" . number_format($minAmount) . ", Max: $" . number_format($maxAmount) . "):", "1000");
        $player->sendForm($form);
    }
    
    private function openSimpleBankNoteUI(Player $player): void {
        $player->sendMessage(TextFormat::GREEN . "§l§a=== BankNote Manager ===");
        $player->sendMessage(TextFormat::GRAY . "Available commands:");
        $player->sendMessage(TextFormat::YELLOW . "/banknote <amount> " . TextFormat::GRAY . "- Create a banknote");
        
        $quickAmounts = $this->getConfig()->getNested("ui.quick_amounts", [100, 500, 1000, 5000, 10000, 50000]);
        $player->sendMessage(TextFormat::GREEN . "Quick amounts:");
        
        $amountLine = "";
        foreach ($quickAmounts as $amount) {
            $amountLine .= TextFormat::AQUA . "$" . number_format($amount) . TextFormat::GRAY . " | ";
        }
        $player->sendMessage(rtrim($amountLine, " | "));
        
        $player->sendMessage(TextFormat::GRAY . "Click on the amounts above or type /banknote <amount>");
    }
    
    private function processBankNoteCreation(Player $sender, float $amount): void {
        $this->getMoney($sender, function(float $money) use ($sender, $amount): void {
            $playerName = $sender->getName();
            
            if ($money < $amount) {
                $sender->sendMessage(TextFormat::RED . "You don't have enough money! You need $" . number_format($amount, 2) . " but only have $" . number_format($money, 2));
                return;
            }

            $itemType = $this->getConfig()->getNested("banknote.item.type", "paper");
            $testItem = $this->getItemFromConfig($itemType);
            
            if ($sender->getInventory()->canAddItem($testItem)) {
                $this->reduceMoney($sender, $amount, function(bool $success) use ($sender, $amount, $playerName): void {
                    if ($success) {
                        $this->createBankNoteItem($sender, $amount, $playerName);
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Failed to deduct money! Please try again.");
                    }
                });
            } else {
                $sender->sendMessage(TextFormat::RED . "Your inventory is full! Please make some space.");
            }
        });
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $playerName = $player->getName();
        $block = $event->getBlock();
        
        if ($event->isCancelled()) {
            return;
        }
        
        if ($block instanceof ItemFrame || 
            $block instanceof Chest || 
            $block instanceof EnderChest ||
            $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            return;
        }
        
        $currentTime = time();
        $cooldownTime = $this->getConfig()->getNested("banknote.limits.cooldown", 1);
        if (isset($this->cooldowns[$playerName]) && ($currentTime - $this->cooldowns[$playerName]) < $cooldownTime) {
            return;
        }
        
        $nbt = $item->getNamedTag();
        if (!$nbt->hasTag("BankNoteAmount")) {
            return;
        }
        
        $this->cooldowns[$playerName] = $currentTime;
        
        if (!$nbt->hasTag("BankNoteCreator") || !$nbt->hasTag("BankNoteDate")) {
            $player->sendMessage(TextFormat::RED . "This banknote appears to be corrupted!");
            return;
        }
        
        $amount = $nbt->getFloat("BankNoteAmount");
        $creator = $nbt->getString("BankNoteCreator", "Unknown");
        $date = $nbt->getString("BankNoteDate", "Unknown");
        
        $maxAmount = $this->getConfig()->getNested("banknote.limits.maximum", 1000000);
        if ($amount <= 0 || $amount > $maxAmount) {
            $player->sendMessage(TextFormat::RED . "This banknote appears to be invalid!");
            return;
        }
        
        $configItemType = $this->getConfig()->getNested("banknote.item.type", "paper");
        $expectedItem = $this->getItemFromConfig($configItemType);
        
        if ($item->getTypeId() !== $expectedItem->getTypeId()) {
            $player->sendMessage(TextFormat::RED . "Invalid banknote item!");
            return;
        }
        
        $event->cancel();
        
        $inventory = $player->getInventory();
        $slot = $inventory->getHeldItemIndex();
        $heldItem = $inventory->getItem($slot);
        
        if (!$heldItem->equals($item, true, true) || $heldItem->getCount() < 1) {
            $player->sendMessage(TextFormat::RED . "Banknote validation failed!");
            return;
        }
        
        $newItem = clone $heldItem;
        $newItem->setCount($heldItem->getCount() - 1);
        $inventory->setItem($slot, $newItem);
        
        $this->addMoney($player, $amount, function(bool $success) use ($player, $amount, $creator, $date, $inventory, $slot, $heldItem): void {
            if (!$success) {
                $restoreItem = clone $heldItem;
                $inventory->setItem($slot, $restoreItem);
                $player->sendMessage(TextFormat::RED . "Failed to redeem banknote! Item has been restored.");
                return;
            }
            
            $player->sendMessage(TextFormat::GREEN . "You have successfully redeemed $" . number_format($amount, 2) . " from a banknote!");
            $player->sendMessage(TextFormat::GRAY . "Originally created by: " . TextFormat::WHITE . $creator);
            $player->sendMessage(TextFormat::GRAY . "Date of creation: " . TextFormat::WHITE . $date);
        });
    }
}
