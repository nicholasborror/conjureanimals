<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conjure Animals Tracker</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
            position: relative;
        }
        h1 {
            text-align: center;
            color: #444;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .animal {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
        }
        label {
            margin-right: 10px;
            font-weight: bold;
            display: inline-block;
        }
        input[type="number"] {
            width: 50px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            padding: 5px;
        }
        button {
            background-color: #5c6bc0;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #3949ab;
        }
        select {
            padding: 5px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .advantage {
            font-weight: bold;
            color: red;
        }
        .attack-result {
            color: blue;
            display: block;
            margin-top: 5px;
        }
        .disadvantage {
            color: green;
        }
        .damage-result {
            color: red;
            display: block;
            margin-top: 5px;
        }
        .critical {
            font-weight: bold;
        }
        .settings, .roll-controls {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .github-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .github-link img {
            width: 32px;
            height: 32px;
        }
    </style>
</head>
<body>

    <a href="https://github.com/nicholasborror/conjureanimals" target="_blank" class="github-link">
        <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub">
    </a>

    <h1>Conjure Animals Tracker</h1>
    
    <div class="container">
        <div class="settings">
            <label for="initial-hp">Set Initial Hit Points for All Animals:</label>
            <input type="number" id="initial-hp" value="10" min="1" max="100">
            <button onclick="setInitialHitPoints()">Apply</button>
            <button style="margin-left: auto;" onclick="resetForm()">Reset</button>
        </div>

        <div class="roll-controls">
            <div>
                <label for="attack-bonus">Attack Bonus:</label>
                <input type="number" id="attack-bonus" placeholder="0" min="0">
                <button onclick="rollAllAttacks()">Roll All Attacks</button>
            </div>
            <div style="margin-left: auto;">
                <label for="damage-roll">Damage Dice:</label>
                <select id="damage-roll">
                    <option value="1d4">1d4</option>
                    <option value="2d4">2d4</option>
                    <option value="3d4">3d4</option>
                    <option value="1d6">1d6</option>
                    <option value="2d6">2d6</option>
                    <option value="1d8">1d8</option>
                </select>
                <input type="number" id="additional-damage" placeholder="Additional Damage" min="0">
                <button onclick="rollAllDamages()">Roll All Damages</button>
            </div>
        </div>

        <!-- Loop to create animal entries -->
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <div class="animal" id="animal-<?= $i ?>">
            <h2>Animal <?= $i ?></h2>
            <label>Hit Points:
                <input type="number" id="hp-<?= $i ?>" value="10" readonly>
            </label>
            <button onclick="adjustHP(<?= $i ?>, 1)">+1</button>
            <button onclick="adjustHP(<?= $i ?>, -1)">-1</button>

            <div>
                <label><input type="checkbox" id="advantage-<?= $i ?>"> Advantage</label>
                <label><input type="checkbox" id="disadvantage-<?= $i ?>"> Disadvantage</label>
                <button onclick="rollAttack(<?= $i ?>)">Roll Attack</button>
                <span id="attack-roll-<?= $i ?>" class="attack-result"></span>
            </div>

            <div>
                <span id="damage-result-<?= $i ?>" class="damage-result">Damage: </span>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <script>
        function setInitialHitPoints() {
            const initialHp = parseInt(document.getElementById('initial-hp').value);
            if (isNaN(initialHp) || initialHp < 1 || initialHp > 100) {
                alert("Please enter a valid number for initial hit points (1-100).");
                return;
            }
            for (let i = 1; i <= 8; i++) {
                document.getElementById(`hp-${i}`).value = initialHp;
                document.getElementById(`animal-${i}`).style.display = 'block'; // Ensure visibility reset
            }
        }

        function adjustHP(animalNumber, amount) {
            const hpField = document.getElementById(`hp-${animalNumber}`);
            let currentHp = parseInt(hpField.value, 10);
            currentHp += amount;
            hpField.value = currentHp;

            if (currentHp <= 0) {
                document.getElementById(`animal-${animalNumber}`).style.display = 'none';
            }
        }

        function rollAttack(animalNumber, attackBonus = 0) {
            const hasAdvantage = document.getElementById(`advantage-${animalNumber}`).checked;
            const hasDisadvantage = document.getElementById(`disadvantage-${animalNumber}`).checked;
            const roll1 = Math.floor(Math.random() * 20) + 1;
            let roll2 = null;
            let finalRoll = roll1;

            if (hasAdvantage && !hasDisadvantage) {
                roll2 = Math.floor(Math.random() * 20) + 1;
                finalRoll = Math.max(roll1, roll2);
            } else if (!hasAdvantage && hasDisadvantage) {
                roll2 = Math.floor(Math.random() * 20) + 1;
                finalRoll = Math.min(roll1, roll2);
            }

            finalRoll += attackBonus;  // Apply attack bonus

            // Clear damage results upon new attack roll
            clearDamageResult(animalNumber);

            // Display the attack result
            displayAttackResult(animalNumber, roll1, roll2, finalRoll, hasAdvantage, hasDisadvantage);

            // Mark critical for damage roll if initial roll without bonus is 20
            document.getElementById(`damage-result-${animalNumber}`).dataset.critical = roll1 === 20 || roll2 === 20 ? 'true' : 'false';
        }

        function rollAllAttacks() {
            const attackBonus = parseInt(document.getElementById('attack-bonus').value, 10);
            if (isNaN(attackBonus) || attackBonus < 0) {
                alert("Please enter a valid attack bonus (0 or greater).");
                return;
            }
            for (let i = 1; i <= 8; i++) {
                rollAttack(i, attackBonus);
            }
        }

        function displayAttackResult(animalNumber, roll1, roll2, finalRoll, hasAdvantage, hasDisadvantage) {
            const attackResult = document.getElementById(`attack-roll-${animalNumber}`);
            attackResult.innerText = `Roll: ${roll1}${roll2 !== null ? `, ${roll2}` : ''} - Result: ${finalRoll}`;
            attackResult.className = `attack-result ${hasAdvantage ? 'advantage' : ''} ${hasDisadvantage ? 'disadvantage' : ''}`;
        }

        function rollAllDamages() {
            const damageSelect = document.getElementById('damage-roll').value;
            const [diceRolled, diceType] = damageSelect.split('d').map(Number);
            const additionalDamage = parseInt(document.getElementById('additional-damage').value, 10);
            if (isNaN(additionalDamage) || additionalDamage < 0) {
                alert("Please enter a valid additional damage value (0 or greater).");
                return;
            }

            for (let i = 1; i <= 8; i++) {
                rollDamage(i, diceRolled, diceType, additionalDamage);
            }
        }

        function rollDamage(animalNumber, diceRolled, diceType, additionalDamage) {
            let damageTotal = 0;
            let diceRollsStr = '';
            const isCritical = document.getElementById(`damage-result-${animalNumber}`).dataset.critical === 'true';

            for (let j = 0; j < diceRolled; j++) {
                const roll = Math.floor(Math.random() * diceType) + 1;
                damageTotal += roll;
                diceRollsStr += `${roll} `;
            }
            
            damageTotal += additionalDamage;
            const displayedTotal = isCritical ? damageTotal * 2 : damageTotal;
            const damageResult = document.getElementById(`damage-result-${animalNumber}`);
            damageResult.innerText = `Damage: ${displayedTotal} [${diceRollsStr.trim()}]${isCritical ? ' (Critical!)' : ''}`;
            damageResult.className = isCritical ? 'damage-result critical' : 'damage-result';
        }

        function clearDamageResult(animalNumber) {
            const damageResult = document.getElementById(`damage-result-${animalNumber}`);
            damageResult.innerText = 'Damage: ';
            damageResult.dataset.critical = 'false'; // Reset critical marker
        }

        function resetForm() {
            // Reset initial hit points
            document.getElementById('initial-hp').value = 10;
            setInitialHitPoints();

            // Reset attack and damage inputs
            document.getElementById('attack-bonus').value = '';
            document.getElementById('damage-roll').value = '1d4';
            document.getElementById('additional-damage').value = '';

            // Reset animal-specific fields
            for (let i = 1; i <= 8; i++) {
                document.getElementById(`advantage-${i}`).checked = false;
                document.getElementById(`disadvantage-${i}`).checked = false;
                clearDamageResult(i);
                const attackResult = document.getElementById(`attack-roll-${i}`);
                attackResult.innerText = '';
            }
        }
    </script>

</body>
</html>