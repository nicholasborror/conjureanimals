<?php?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Conjure Animals Tracker</title>
  <style>
    body { font-family: sans-serif; max-width: 800px; margin: 20px auto; }
    h1 { text-align: center; color: #444; }
    .settings { display: flex; align-items: center; margin-bottom: 20px; }
    .settings > * { margin-right: 16px; }
    label { margin-right: 8px; }
    select { margin-right: 8px; }
    .animal { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
    .animal.dead { color: lightgrey; }
    .stats, .notes, .combat { margin-top: 8px; }
    .stats p, .notes p, .attack-result, .damage-result { margin: 4px 0; }
    .notes p { font-style: italic; color: #555; }
    .notes a { color: #007bff; text-decoration: none; }
    .attack-result.advantage { font-weight: bold; color: green; }
    .attack-result.disadvantage { font-weight: bold; color: orange; }
    .attack-result.critical { font-weight: bold; color: red; }
    .damage-result.critical { font-weight: bold; color: red; }
    .hp-controls button { margin: 0 4px; }
    .github-link { position: absolute; top: 10px; right: 10px; z-index: 1000; }
    .github-link img { width: 24px; height: 24px; opacity: 0.6; transition: opacity 0.2s ease; }
    .github-link img:hover {opacity: 1; }
  </style>
</head>
<body>
        <a href="https://github.com/nicholasborror/conjureanimals" target="_blank" class="github-link">
        <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub" width="24" height="24">
        </a>
        
  <h1>Conjure Animals Tracker</h1>

  <div class="settings">
    <button onclick="resetForm()">Reset All</button>
  </div>

  <div class="settings">
    <label for="cr-select">Summon:</label>
    <select id="cr-select" disabled>
      <option value="">-- Select CR --</option>
      <option value="2">1 × CR 2 or lower</option>
      <option value="1">2 × CR 1 or lower</option>
      <option value="0.5">4 × CR ½ or lower</option>
      <option value="0.25">8 × CR ¼ or lower</option>
    </select>

    <label for="animal-select">Beast:</label>
    <select id="animal-select" disabled>
      <option>Select a CR first</option>
    </select>
  </div>

  <div id="animals-container">
    <?php for ($i = 1; $i <= 8; $i++): ?>
      <div class="animal" id="animal-<?php echo $i; ?>" style="display: none;">
        <h2><span class="title">—</span></h2>
        <div class="stats">
          <p class="ac">AC: —</p>
          <p class="speed">Speed: —</p>
          <p class="hp">HP: —</p>
          <div class="attacks"></div>
        </div>
        <div class="notes"></div>
        <div class="combat"></div>
      </div>
    <?php endfor; ?>
  </div>

  <script>
    const countMap = { "2":1, "1":2, "0.5":4, "0.25":8 };
    let creatures = {};
    const crSelect = document.getElementById('cr-select');
    const animalSelect = document.getElementById('animal-select');

    fetch('animals.json')
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(data => { creatures = data; crSelect.disabled = false; })
      .catch(e => { console.error(e); alert('Couldn’t load animals.json'); });

    crSelect.addEventListener('change', () => {
      const crVal = crSelect.value;
      let list = creatures[crVal] || [];
      if (crVal === '0.25' && creatures['0']) list = list.concat(creatures['0']);
      list.sort((a,b) => a.name.localeCompare(b.name));
      animalSelect.innerHTML = '';
      list.forEach(b => {
        const o = document.createElement('option');
        o.value = b.name; o.textContent = b.name;
        animalSelect.appendChild(o);
      });
      animalSelect.disabled = list.length === 0;
      for (let i = 1; i <= 8; i++) {
        document.getElementById(`animal-${i}`).style.display =
          (countMap[crVal] && i <= countMap[crVal]) ? 'block' : 'none';
      }
      animalSelect.dispatchEvent(new Event('change'));
    });

    animalSelect.addEventListener('change', () => {
      const crVal = crSelect.value;
      const name = animalSelect.value;
      let list = creatures[crVal] || [];
      if (crVal === '0.25' && creatures['0']) list = list.concat(creatures['0']);
      const beast = list.find(b => b.name === name);
      if (!beast) return;
      const num = countMap[crVal];
      for (let i = 1; i <= num; i++) {
        const box = document.getElementById(`animal-${i}`);
        box.classList.remove('dead');
        box.querySelector('.title').textContent = `${name} ${i}`;
        box.querySelector('.ac').textContent    = 'AC: ' + beast.ac;
        box.querySelector('.speed').textContent = 'Speed: ' + beast.speed;
        box.querySelector('.hp').innerHTML =
          `HP: <span id="hp-value-${i}">${beast.hp}</span>` +
          `<span class="hp-controls">` +
          `<button onclick="adjustHP(${i}, -1)">-</button>` +
          `<button onclick="adjustHP(${i}, +1)">+</button>` +
          `</span>`;
        const attDiv = box.querySelector('.attacks'); attDiv.innerHTML = '';
        beast.attacks.forEach((atk,a) => {
          const div = document.createElement('div');
          div.innerHTML = `
            <p><strong>${atk.name}.</strong> +${atk.bonus} to hit, ${atk.damage} ${atk.description}</p>
            <label><input type="checkbox" id="adv-${i}-${a}"
              onchange="if(this.checked)document.getElementById('dis-${i}-${a}').checked=false;">Adv</label>
            <label><input type="checkbox" id="dis-${i}-${a}"
              onchange="if(this.checked)document.getElementById('adv-${i}-${a}').checked=false;">Dis</label>
            <button onclick="rollAttack(${i},${a},${atk.bonus})">Attack</button>
            <span id="atk-roll-${i}-${a}" class="attack-result"></span>
            <button onclick="rollDamage(${i},${a},'${atk.damage}')">Damage</button>
            <span id="dmg-roll-${i}-${a}" class="damage-result" data-critical="false">Damage:</span>
          `;
          attDiv.appendChild(div);
        });
        const notesDiv = box.querySelector('.notes');
        let url = beast.notes.startsWith('Source: ') ? beast.notes.slice(8) : beast.notes;
        notesDiv.innerHTML = `<p>Source: <a href="${url}" target="_blank">${url}</a></p>`;
        box.querySelectorAll('.attack-result, .damage-result').forEach(el => el.innerText = '');
        box.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
      }
    });

    function adjustHP(index, delta) {
      const span = document.getElementById(`hp-value-${index}`);
      let val = parseInt(span.textContent, 10) || 0;
      val = Math.max(0, val + delta);
      span.textContent = val;
      const box = document.getElementById(`animal-${index}`);
      box.classList.toggle('dead', val === 0);
    }

    function rollAttack(n,a,bonus=0) {
      const adv = document.getElementById(`adv-${n}-${a}`).checked;
      const dis = document.getElementById(`dis-${n}-${a}`).checked;
      const r1 = Math.floor(Math.random()*20)+1;
      let r2 = null, fin = r1;
      if (adv && !dis) { r2 = Math.floor(Math.random()*20)+1; fin = Math.max(r1,r2); }
      else if (!adv && dis) { r2 = Math.floor(Math.random()*20)+1; fin = Math.min(r1,r2); }
      const crit = (fin === 20);
      const total = fin + bonus;
      const span = document.getElementById(`atk-roll-${n}-${a}`);
      span.innerText = r2 !== null
        ? `Rolls: ${r1}, ${r2} → ${fin} +${bonus} = ${total}`
        : `Roll: ${fin} +${bonus} = ${total}`;
      span.className = `attack-result ${adv?'advantage':''} ${dis?'disadvantage':''} ${crit?'critical':''}`;
      document.getElementById(`dmg-roll-${n}-${a}`).dataset.critical = crit?'true':'false';
    }

    function rollDamage(n,a,dmg) {
      const m = dmg.match(/(\d+)d(\d+)([+-]\d+)?/);
      if (!m) return;
      const cnt = parseInt(m[1],10), typ = parseInt(m[2],10), add = parseInt(m[3]||'0',10);
      let total = 0, rolls = [];
      for (let j = 0; j < cnt; j++) {
        const r = Math.floor(Math.random()*typ)+1;
        rolls.push(r);
        total += r;
      }
      total += add;
      const crit = document.getElementById(`dmg-roll-${n}-${a}`).dataset.critical === 'true';
      const disp = crit ? total*2 : total;
      const span = document.getElementById(`dmg-roll-${n}-${a}`);
      span.innerText = `Damage: ${disp} [${rolls.join(', ')}${add?` +${add}`:''}]${crit?' (CRIT!)':''}`;
      span.className = crit?'damage-result critical':'damage-result';
    }

    function resetForm() {
      crSelect.value = '';
      animalSelect.innerHTML = '<option>Select a CR first</option>';
      animalSelect.disabled = true;
      for (let i = 1; i <= 8; i++) {
        const box = document.getElementById(`animal-${i}`);
        box.style.display = 'none';
        box.classList.remove('dead');
        box.querySelector('.title').textContent = '—';
        box.querySelector('.ac').textContent = 'AC: —';
        box.querySelector('.speed').textContent = 'Speed: —';
        box.querySelector('.hp').innerHTML = 'HP: —';
        box.querySelector('.attacks').innerHTML = '';
        box.querySelector('.notes').innerHTML = '';
      }
    }
  </script>
  <br><br><br><br><br><br>
<h1>Best Conjure Animals — Quick Notes</h1>
*Has useful attacks/drop/grapple/poison/prone/shove against those requiring magical attacks, e.g. grapple requires using up their action to escape, prone give advantage to attacks against, etc.
<h2>1 × CR 2</h2>
<ul>
  <li><strong>AC:</strong> G Elk (14)</li>
  <li><strong>DMG &amp; ATK:</strong> Polar Bear 2+7, S Tooth 2+6</li>
  <li><strong>Fastest:</strong> G Elk 60'</li>
  <li><strong>*Grapple:</strong> G Constrictor</li>
  <li><strong>HP:</strong> G Constrict (60), S Tooth (52)</li>
  <li><strong>*Prone:</strong> Rhino 15@20', G Elk 14@20'</li>
  <li><strong>Reach:</strong> G Elk (10), G Constric (10)</li>
  <li><strong>*Shove:</strong> Rhino, Polar Bear</li>
</ul>

<h2>2 × CR 1</h2>
<ul>
  <li><strong>AC:</strong> D Worf (14), G Spider (14)</li>
  <li><strong>Climb:</strong> G Spider</li>
  <li><strong>Darkvision:</strong> Tiger, G Spider</li>
  <li><strong>DMG &amp; ATK:</strong> B Bear, G Eagle, G Vulture</li>
  <li><strong>*Drop:</strong> G Eagle+5, G Vulture+4 (pack adv)</li>
  <li><strong>Fastest:</strong> G Eagle (80)</li>
  <li><strong>*Grapple:</strong> Giant Octopus, Giant Toad</li>
  <li><strong>HP:</strong> G Oct (52), Giant Toad (39)</li>
  <li><strong>Jump:</strong> Lion (25)</li>
  <li><strong>Pack:</strong> D Wolf, G Vulture</li>
  <li><strong>*Poison:</strong> Giant Spider (+web), </li>
  <li><strong>*Prone:</strong> Dire Wolf </li>
  <li><strong>Range:</strong> G Spider (30' once)</li>
  <li><strong>Reach:</strong> G Octopus (15)</li>
  <li><strong>See Invisibility:</strong> G
  <li><strong>*Shove:</strong> Brown Bear</li>
  <li><strong>Stealth:</strong> Tiger</li>
</ul>

<h2>4 × CR 1/2</h2>
<ul>
  <li><strong>AC:</strong> all 11/12</li>
  <li><strong>ATK &amp; DMG:</strong> Ape, B Bear</li>
  <li><strong>Ranged:</strong> Ape</li>
  <li><strong>*Grapple:</strong> Ape, Croc</li>
  <li><strong>Fastest:</strong> Warhorse (60), Giant Wasp (50)</li>
  <li><strong>HP:</strong> all ~19</li>
  <li><strong>*Poison:</strong> Giant Wasp</li>
  <li><strong>*Prone:</strong> Warhorse 14@20', Giant Goal 13@20'</li>
  <li><strong>*Shove:</strong> Ape</li>
  <li><strong>*Transport:</strong> Warhorse (13.6 miles/hr)</li>
</ul>

<h2>8 × CR 1/4</h2>
<ul>
  <li><strong>AC:</strong> F Snake (14), GP Snake (14)</li>
  <li><strong>ATK:</strong> Badger 2+3</li>
  <li><strong>Darkvision:</strong> GW Spider, G Lizard</li>
  <li><strong>*Drainblood:</strong> Stirge</li>
  <li><strong>DMG:</strong> Elk, D Horse, F Snake</li>
  <li><strong>Fastest:</strong> R Horse (60), F Snake (60)</li>
  <li><strong>Flyby:</strong> F Snake</li>
  <li><strong>*Grapple:</strong> Const Snake, G Frog, Octo</li>
  <li><strong>HP:</strong> G Bat (22), Draft Horse (19)</li>
  <li><strong>Pack &amp; Prone:</strong> Wolf</li>
  <li><strong>*Poison:</strong> Flying Snake, Giant PSnake, Giant Cent, Giant WSpider</li>
  <li><strong>*Prone:</strong> Elk 13@20', Panther 12@20'</li>
  <li><strong>See Invisibility:</strong> G Bat, G Cent, F Snake</li>
  <li><strong>*Shove:</strong> Draft Horse</li>
  <li><strong>Stealth:</strong> Panther, Wolf</li>
  <li><strong>*Transport:</strong> Axe Beak (11 miles/hr)</li>
</ul>

</body>
</html>
