# AdvancedItemEffectsPE
Add effects to items or enchantments!
Adding an effect is global, any tools with that enchantment will give the player the specified effect
Adding an effect to an item will only apply it to that item

#Commands
Add effect to item: /aie ati Effect Id Duration Amplifier

Delete effect from item: /aie dfi

Add to enchantment: /aie ate Effect Id Duration Amplifier

Delete from enchantment: /aie dfe

#Note
One enchantment (not including level) can only be assigned one effect, so Durability I and Durability II can have different effects
Effects will only be applied when a player holds the item(PlayerItemHeldEvent)
