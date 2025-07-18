# ConjureAnimals

A lightweight web tool to manage **Conjure Animals** summons in Dungeons & Dragons 5th Edition (2014 rules, before they screwed it up in 2024). 
Select a challenge rating (CR), pick a beast, and instantly generate its stat block(s) with interactive combat controls to streamline gameplay.

> ⚠️ Designed for personal use and optimized for simplicity.

## Demo

👉 [Live Demo](https://conjureanimals.com)

![Screenshot](/screenshot.png?raw=true "Screenshot")


## Features

* **Dynamic CR filtering**: Choose 1× CR 2, 2× CR 1, 4× CR ½, or 8× CR ¼ or lower to match spell allowances.
* **Alpha-sorted dropdown**: All beasts of the selected CR (and CR 0 under CR ¼) populate in alphabetical order.
* **Stat block display**: Auto-fills AC, Speed, and HP for each summoned creature.
* **HP controls**: Increment or decrement HP; when HP reaches 0, the entire block dims to light grey to indicate a fallen beast.
* **Attack rolls**: Click to roll attack with advantage/disadvantage options; displays individual dice, final result, and highlights criticals in bold red.
* **Damage rolls**: Roll weapon damage, auto-double on crits, and show individual dice breakdown.
* **Source links**: Each creature’s notes field contains a direct D&D Beyond URL for quick reference.
* **Reset function**: Clear all selections and reset the interface in one click.

## Installation

1. Clone or download this repository.
2. Place `default.php` and `animals.json` in your web server directory.
3. Ensure your server supports PHP and has file access to both files.

## Usage

* Navigate to `default.php` in your browser.
* Select the desired CR from the **Summon** dropdown.
* Choose a beast from the **Beast** dropdown.
* Interact with stat blocks:
  * Use **+**/**−** to adjust HP.
  * Click **Attack** to roll to hit.
  * Click **Damage** to roll damage.
* Click **Reset All** to start over.

## Customization

**Updating beasts**: Edit `animals.json` to add, remove, or adjust creatures. Ensure `notes` include the `Source: https://www.dndbeyond.com/monsters/<slug>` format.

## Contributing

Feel free to submit issues or pull requests for bug fixes, new features, or updated creature data. Please keep JSON formatting consistent.

## License

Distributed under the MIT License. See `LICENSE` for details.

## Credits

Project assisted by ChatGPT AI.
