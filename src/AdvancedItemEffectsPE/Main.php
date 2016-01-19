<?php

namespace AdvancedItemEffectsPE;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;
use pocketmine\Player;
use pocketmine\nbt\tag\Compound;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Armor;
use pocketmine\entity\Effect;
use pocketmine\utils\Config;
use pocketmine\event\TranslationContainer;

class Main extends PluginBase implements Listener{

	public $activeEffects = array();
	
    public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveResource("data.yml");
		$this->data = new Config($this->getDataFolder()."data.yml", Config::YAML, array());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if($sender->hasPermission("AdvancedItemEffects.command")){
			if(!($sender instanceof Player)){
				$sender->sendMessage("This command can only be used in-game");
				return true;
			}
			if(!isset($args[0])) $args[0] = "help";
			$inv = $sender->getInventory();
			$hand = $inv->getItemInHand();
			switch(strtolower($args[0])){
				case "addtoitem":
				case "ati":
					if(count($args) !== 4){
						$sender->sendMessage("Usage: /aie <addToItem/ati> <Effect Id> <Duration> <Amplifier>");
						return true;
					}elseif(!is_numeric($args[1]) || !is_numeric($args[2]) || !is_numeric($args[3])){
						$sender->sendMessage("Usage: /aie <addToItem/ati> <Effect Id> <Duration> <Amplifier>");
						return true;
					}elseif($hand->getId() === 0){
						$sender->sendMessage("Please hold the item in hand");
						return true;
					}
					$nbt = $hand->getNamedTag();
					if(!($nbt instanceof Compound)){
						$nbt = new Compound("", []);					
					}
					$args[2] *= 20;
					array_shift($args);
					$nbt->AdvancedItemEffects = new String("AdvancedItemEffects", implode(" ",$args));
					$hand->setNamedTag($nbt);
					$inv->setItemInHand($hand);
					$sender->sendMessage("Effect added to this item");
				break;
				case "delfromitem":
				case "dfi":
					if($hand->getId() === 0){
						$sender->sendMessage("Please hold the item in hand");
						return true;
					}
					$nbt = $hand->getNamedTag();
					if(!($nbt instanceof Compound)){
						$sender->sendMessage("That item does not have an NBT tag");
						return true;				
					}
					if(!isset($nbt->AdvancedItemEffects)){
						$sender->sendMessage("That item does not have any custom enchantment");
						return;
					}
					unset($nbt->AdvancedItemEffects);
					$hand->setNamedTag($nbt);
					$inv->setItemInHand($hand);
					$sender->sendMessage("Effect removed from this item");
				break;
				case "addtoenchantment":
				case "ate":
					if(count($args) !== 4){
						$sender->sendMessage("Usage: /aie <addToEnchantment/ate> <Effect Id> <Duration> Amplifier>");
						return true;
					}elseif(!is_numeric($args[1]) || !is_numeric($args[2]) || !is_numeric($args[3])){
						$sender->sendMessage("Usage: /aie <addToEnchantment/ate> <Effect Id> <Duration> <Amplifier>");
						return true;
					}elseif($hand->getId() === 0){
						$sender->sendMessage("Please hold the item in hand");
						return true;
					}
					$nbt = $hand->getNamedTag();
					if($hand->getEnchantments() === []){
						$sender->sendMessage("That item does not have any enchantments");
						return true;				
					}
					$args[2] *= 20;
					array_shift($args);
					foreach($hand->getEnchantments() as $e){
						$this->data->set($e->getId()." ".$e->getLevel(),implode(" ",$args));
						$sender->sendMessage("Added effect for ".new TranslationContainer($e->getName())." {$e->getLevel()}");
					}
					$this->data->save();
				break;
				case "delfromenchantment":
				case "dfe":
					if($hand->getId() === 0){
						$sender->sendMessage("Please hold the item in hand");
						return true;
					}
					if($hand->getEnchantments() === []){
						$sender->sendMessage("That item does not have any enchantments");
						return true;				
					}
					foreach($hand->getEnchantments() as $e){
						$this->data->remove($e->getId()." ".$e->getLevel());
						$sender->sendMessage("Removed effect for ".new TranslationContainer($e->getName())." {$e->getLevel()}");
					}
					$this->data->save();
				break;
				case "help":
					$sender->sendMessage("AdvancedItemEffectsPE Commands\nAdd effect to item: /aie <ati> <Effect Id> <Duration> <Amplifier>\nDelete effect from item: /aie <dfi>\nAdd to enchantment: /aie <ate> <Effect Id> <Duration> <Amplifier>\n Delete from enchantment: /aie <dfe>");
				break;
			}
		}
		return true;
	}
	
	public function giveEffect($enchant,$player = null){
		$data = explode(' ',$enchant);
		$this->activeEffects[$player->getName()][] = $data[0];
		$effect = Effect::getEffect($data[0]);
		if($effect) $player->addEffect($effect->setDuration($data[1])->setAmplifier($data[2]));
	}
	
	public function resetEffects($player){
		$name = $player->getName();
		if(isset($this->activeEffects[$name])){
			foreach($this->activeEffects[$name] as $effectId){
				$player->removeEffect($effectId);
			}
			unset($this->activeEffects[$name]);
		}
	}
	
	//-----------------------------------------------------------------------
	
	public function onHold(PlayerItemHeldEvent $event){
		$p = $event->getPlayer();
		$item = $event->getItem();
		$this->resetEffects($p);
		if($item->hasCompoundTag()){
			$nbt = $item->getNamedTag();
			if(isset($nbt->AdvancedItemEffects)){
				$enchant = $nbt->AdvancedItemEffects;
				$this->giveEffect($enchant,$p);
			}
			if($item->getEnchantments() !== []){
				foreach($item->getEnchantments() as $e){
					if(($enchant = $this->data->get($e->getId()." ".$e->getLevel())) !== null){
						$this->giveEffect($enchant,$p);
					}
				}
			}
			
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		$this->resetEffects($event->getPlayer());
	}
}
