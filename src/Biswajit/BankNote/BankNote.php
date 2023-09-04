<?php

namespace Biswajit\BankNote;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use davidglitch04\libEco\libEco;

class BankNote extends PluginBase implements Listener{

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "banknote") {
            if (isset($args[0]) && is_numeric($args[0]) && $args[0] > 0) {
                
                $amount = (float) $args[0];
                libEco::myMoney($sender, function(float $money) use ($sender, $amount): void {
                $playerName = $sender->getName();
                if ($money < $amount) {
                $sender->sendMessage("§cYou Don't Have Ignore Money!");
                }else{
                libEco::reduceMoney($sender, $amount, function() : void {});
                $item = VanillaItems::PAPER();
                $item->setCustomName("§r§l§6$" . $amount . " §aBANK NOTE");
                $item->setLore(["§r§7Right Click To Redeem This §aBank Note§7\n§r§7Withdrawn By §f" . $playerName . "\n§r§7Date »" . date("§f d/m/y") . "\n\n§r§7Value » §a$" . $amount]);
                $item->getNamedTag()->setFloat("Amount", $amount);
                $sender->getInventory()->addItem($item);
                $sender->sendMessage("§7You have been convert $amount to bank note.");
                     }
                  });
                return true;
            } else {
                $sender->sendMessage("Usage: /banknote {amount}");
                return true;
            }
        }
        return false;
    }
        public function onPlayerInteract(PlayerInteractEvent $event): void
           {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getNamedTag()->getTag("Amount") !== null) {
            $amount = $item->getNamedTag()->getFloat("Amount");

            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);

            libEco::addMoney($player, $amount);
            $player->sendMessage("§7You Have Claimed §e$" . $amount . "§7 Note!");
        }
    }
}
