<?php

declare(strict_types=1);

namespace xBeastMode\WorldInventory;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\TextFormat;
final class ItemParser{
        /**
         * Parses items from string using item names.
         * Legacy item ids no longer supported.
         *
         * @param Item[]|string[] $items
         *
         * @return Item[]
         */
        public static function parse(array $items): array{
                $outputItems = [];
                foreach($items as $item){
                        if(!$item instanceof Item){
                                if(!is_string($item)) continue;

                                $parts = explode(":", $item);

                                $itemName = array_shift($parts);
                                $amount = (int) array_shift($parts);
                                $name = array_shift($parts);
                                $lore = array_shift($parts);

                                $item = StringToItemParser::getInstance()->parse($itemName);
                                if($item === null || $item->isNull()) continue;

                                if(isset($lore) && $lore !== ""){
                                        $item->setLore(explode("\n", TextFormat::colorize($lore)));
                                }

                                $item->setCount($amount);
                                $parts = implode(":", $parts);

                                foreach(self::parseEnchantments([$parts]) as $enchant){
                                        $item->addEnchantment($enchant);
                                }

                                if(isset($name) && strtolower($name) !== "default"){
                                        $item->setCustomName(TextFormat::colorize($name));
                                }
                        }
                        $outputItems[] = $item;
                }

                return $outputItems;
        }

        /**
         * Parse enchantments either by name or id.
         *
         * @param EnchantmentInstance[]|string[] $enchantments
         *
         * @return EnchantmentInstance[]
         */
        public static function parseEnchantments(array $enchantments): array{
                /** @var EnchantmentInstance[] $output */
                $output = [];
                $index = 0;
                /** @var Enchantment $lastEnchantment */
                $lastEnchantment = null;

                foreach($enchantments as $enchantment){
                        if($enchantment instanceof EnchantmentInstance){
                                $output[] = $enchantment;
                        }else{
                                $parts = explode(":", $enchantment);

                                foreach($parts as $part){
                                        if((++$index % 2) === 0){
                                                if($lastEnchantment !== null){
                                                        $output[] = new EnchantmentInstance($lastEnchantment, (int) $part);
                                                }
                                        }else{
                                                $lastEnchantment = StringToEnchantmentParser::getInstance()->parse($part) ?? EnchantmentIdMap::getInstance()->fromId((int) $part);
                                        }
                                }
                        }
                }

                return $output;
        }
}