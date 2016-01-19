# AdvancedItemEffectsPE
Add effects to items or enchantments!
Adding an effect is global, any tools with that enchantment will give the player the specified effect
Adding an effect to an item will only apply it to that item

#Commands
Add effect to item: /aie ati Effect-Id Duration Amplifier

Delete effect from item: /aie dfi

Add to enchantment: /aie ate Effect-Id Duration Amplifier

Delete from enchantment: /aie dfe

#Note
One enchantment (not including level) can only be assigned one effect, so Durability I and Durability II can have different effects

Adding effects to existing enchantments or items will overwrite the effects already set on them

Effects will only be applied when a player holds the item(PlayerItemHeldEvent)

#Copyright
Copyright (C) 2016 wolfdale All Rights Reserved.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
