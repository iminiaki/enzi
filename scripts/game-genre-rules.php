<?php
/**
 * LEGACY (gaming) — not used by Enzi shop. Do not run.
 */
/**
 * Game title / keyword → Persian genre rules for assign-game-genres.php.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical Persian genre terms for pa_game-genre.
 *
 * @return array<int, string>
 */
function diako_game_genre_terms(): array {
	return array(
		'اکشن',
		'ماجراجویی',
		'نقش‌آفرینی',
		'ورزشی',
		'مسابقه‌ای',
		'تیراندازی',
		'استراتژی',
		'ترسناک',
		'مبارزه‌ای',
		'پازل',
		'شبیه‌سازی',
		'خانوادگی',
		'ریتم',
		'پلتفرمر',
		'جهان باز',
		'بقا',
		'متفرقه',
	);
}

/**
 * Exact or high-confidence partial title matches (case-insensitive).
 *
 * Keys are lowercase substrings checked with mb_stripos against the normalized title.
 * Longer / more specific keys should appear first when they could overlap.
 *
 * @return array<string, string>
 */
function diako_game_genre_title_map(): array {
	return array(
		// Sports
		'ea sports fc'           => 'ورزشی',
		'ea sports wrc'          => 'ورزشی',
		'fifa'                   => 'ورزشی',
		'pes 20'                 => 'ورزشی',
		'efootball'              => 'ورزشی',
		'pro evolution soccer'   => 'ورزشی',
		'nba 2k'                 => 'ورزشی',
		'nba2k'                  => 'ورزشی',
		'madden'                 => 'ورزشی',
		'ufc 4'                  => 'ورزشی',
		'ufc 5'                  => 'ورزشی',
		'wwe 2k'                 => 'ورزشی',
		'tony hawk'              => 'ورزشی',
		'mlb the show'           => 'ورزشی',
		'nhl 2'                  => 'ورزشی',
		'pga tour'               => 'ورزشی',
		'football manager'       => 'ورزشی',
		'top spin'               => 'ورزشی',
		'rocket league'          => 'ورزشی',

		// Racing
		'gran turismo'           => 'مسابقه‌ای',
		'forza horizon'          => 'مسابقه‌ای',
		'forza motorsport'       => 'مسابقه‌ای',
		'need for speed'         => 'مسابقه‌ای',
		'mario kart'             => 'مسابقه‌ای',
		'f1 20'                  => 'مسابقه‌ای',
		'f1 24'                  => 'مسابقه‌ای',
		'f1 25'                  => 'مسابقه‌ای',
		'wipeout'                => 'مسابقه‌ای',
		'crash team racing'      => 'مسابقه‌ای',
		'dirt 5'                 => 'مسابقه‌ای',
		'dirt rally'             => 'مسابقه‌ای',
		'assetto corsa'          => 'مسابقه‌ای',
		'ride 5'                 => 'مسابقه‌ای',
		'hot wheels'             => 'مسابقه‌ای',

		// Fighting
		'street fighter 6'       => 'مبارزه‌ای',
		'street fighter v'       => 'مبارزه‌ای',
		'tekken 8'               => 'مبارزه‌ای',
		'tekken 7'               => 'مبارزه‌ای',
		'mortal kombat 1'        => 'مبارزه‌ای',
		'mortal kombat 11'       => 'مبارزه‌ای',
		'guilty gear'            => 'مبارزه‌ای',
		'dragon ball fighterz'   => 'مبارزه‌ای',
		'dragon ball sparking'   => 'مبارزه‌ای',
		'guilty gear'            => 'مبارزه‌ای',
		'king of fighters'       => 'مبارزه‌ای',
		'dead or alive'          => 'مبارزه‌ای',
		'nickelodeon all-star brawl' => 'مبارزه‌ای',
		'multiversus'            => 'مبارزه‌ای',
		'super smash bros'       => 'مبارزه‌ای',

		// Shooters
		'call of duty'           => 'تیراندازی',
		'battlefield 2042'       => 'تیراندازی',
		'battlefield v'          => 'تیراندازی',
		'battlefield 1'          => 'تیراندازی',
		'counter-strike 2'       => 'تیراندازی',
		'counter strike 2'       => 'تیراندازی',
		'cs2'                    => 'تیراندازی',
		'csgo'                   => 'تیراندازی',
		'rainbow six'            => 'تیراندازی',
		'overwatch 2'            => 'تیراندازی',
		'destiny 2'              => 'تیراندازی',
		'halo infinite'          => 'تیراندازی',
		'halo 5'                 => 'تیراندازی',
		'helldivers'             => 'تیراندازی',
		'borderlands 3'          => 'تیراندازی',
		'borderlands 4'          => 'تیراندازی',
		'doom eternal'           => 'تیراندازی',
		'doom 2016'              => 'تیراندازی',
		'far cry'                => 'تیراندازی',
		'metro exodus'           => 'تیراندازی',
		'returnal'               => 'تیراندازی',
		'killzone'               => 'تیراندازی',
		'gears of war'           => 'تیراندازی',
		'warzone'                => 'تیراندازی',
		'apex legends'           => 'تیراندازی',
		'valorant'               => 'تیراندازی',
		'splatoon'               => 'تیراندازی',

		// Horror
		'resident evil'          => 'ترسناک',
		'silent hill'            => 'ترسناک',
		'outlast'                => 'ترسناک',
		'until dawn'             => 'ترسناک',
		'dead space'             => 'ترسناک',
		'alien isolation'        => 'ترسناک',
		'alan wake 2'            => 'ترسناک',
		'the evil within'        => 'ترسناک',
		'little nightmares'      => 'ترسناک',
		'visage'                 => 'ترسناک',
		'the quarry'             => 'ترسناک',
		'the dark pictures'      => 'ترسناک',
		'phasmophobia'           => 'ترسناک',
		'layers of fear'         => 'ترسناک',
		'alone in the dark'      => 'ترسناک',

		// Strategy
		'civilization'           => 'استراتژی',
		'age of empires'         => 'استراتژی',
		'total war'              => 'استراتژی',
		'company of heroes'      => 'استراتژی',
		'starcraft'              => 'استراتژی',
		'command and conquer'    => 'استراتژی',
		'xcom'                   => 'استراتژی',
		'crusader kings'         => 'استراتژی',
		'europa universalis'     => 'استراتژی',
		'hearts of iron'         => 'استراتژی',
		'warhammer 40'           => 'استراتژی',
		'disgaea'                => 'استراتژی',
		'fire emblem'            => 'استراتژی',
		'advance wars'           => 'استراتژی',
		'worms'                  => 'استراتژی',

		// Simulation
		'the sims'               => 'شبیه‌سازی',
		'farming simulator'      => 'شبیه‌سازی',
		'euro truck simulator'   => 'شبیه‌سازی',
		'american truck simulator' => 'شبیه‌سازی',
		'microsoft flight simulator' => 'شبیه‌سازی',
		'planet zoo'             => 'شبیه‌سازی',
		'planet coaster'         => 'شبیه‌سازی',
		'cities skylines'        => 'شبیه‌سازی',
		'two point hospital'     => 'شبیه‌سازی',
		'two point campus'       => 'شبیه‌سازی',
		'powerwash simulator'    => 'شبیه‌سازی',
		'house flipper'          => 'شبیه‌سازی',
		'cooking simulator'      => 'شبیه‌سازی',

		// Puzzle
		'portal 2'               => 'پازل',
		'portal'                   => 'پازل',
		'tetris'                   => 'پازل',
		'limbo'                    => 'پازل',
		'inside'                   => 'پازل',
		'witness'                  => 'پازل',
		'baba is you'              => 'پازل',
		'return of the obra dinn' => 'پازل',
		'unpacking'                => 'پازل',
		'human fall flat'          => 'پازل',
		'bridge constructor'       => 'پازل',

		// Rhythm / party
		'just dance'             => 'ریتم',
		'guitar hero'            => 'ریتم',
		'rock band'              => 'ریتم',
		'beat saber'             => 'ریتم',
		'friday night funkin'    => 'ریتم',

		// Platformer
		'super mario bros'       => 'پلتفرمر',
		'super mario odyssey'    => 'پلتفرمر',
		'super mario 3d'         => 'پلتفرمر',
		'new super mario'        => 'پلتفرمر',
		'crash bandicoot'        => 'پلتفرمر',
		'spyro'                  => 'پلتفرمر',
		'sonic frontiers'        => 'پلتفرمر',
		'sonic origins'          => 'پلتفرمر',
		'ori and the'            => 'پلتفرمر',
		'astro bot'              => 'پلتفرمر',
		'kirby'                  => 'پلتفرمر',
		'donkey kong'            => 'پلتفرمر',
		'rayman'                 => 'پلتفرمر',
		'cuphead'                => 'پلتفرمر',
		'celeste'                => 'پلتفرمر',
		'hollow knight'          => 'پلتفرمر',

		// Survival
		'rust'                   => 'بقا',
		'dayz'                   => 'بقا',
		'ark survival'           => 'بقا',
		'subnautica'             => 'بقا',
		'the forest'             => 'بقا',
		'sons of the forest'     => 'بقا',
		'green hell'             => 'بقا',
		'dont starve'            => 'بقا',
		'valheim'                => 'بقا',
		'7 days to die'          => 'بقا',
		'conan exiles'           => 'بقا',
		'no man\'s sky'          => 'بقا',
		'no mans sky'            => 'بقا',

		// Open world
		'grand theft auto'       => 'جهان باز',
		'gta v'                  => 'جهان باز',
		'gta 5'                  => 'جهان باز',
		'red dead redemption'    => 'جهان باز',
		'elden ring'             => 'جهان باز',
		'cyberpunk 2077'         => 'جهان باز',
		'ghost of tsushima'      => 'جهان باز',
		'ghost of yotei'         => 'جهان باز',
		'horizon forbidden'      => 'جهان باز',
		'horizon zero dawn'      => 'جهان باز',
		'assassin\'s creed'       => 'جهان باز',
		'assassins creed'        => 'جهان باز',
		'watch dogs'             => 'جهان باز',
		'far cry 6'              => 'جهان باز',
		'dying light'            => 'جهان باز',
		'middle-earth'           => 'جهان باز',
		'dragon\'s dogma'        => 'جهان باز',
		'dragons dogma'          => 'جهان باز',
		'starfield'              => 'جهان باز',
		'fallout 4'              => 'جهان باز',
		'fallout 76'             => 'جهان باز',
		'skyrim'                 => 'جهان باز',
		'zelda breath'           => 'جهان باز',
		'zelda tears'            => 'جهان باز',
		'legends of zelda'       => 'جهان باز',

		// RPG
		'final fantasy'          => 'نقش‌آفرینی',
		'dragon quest'           => 'نقش‌آفرینی',
		'persona 5'              => 'نقش‌آفرینی',
		'persona 3'              => 'نقش‌آفرینی',
		'persona 4'              => 'نقش‌آفرینی',
		'the witcher'            => 'نقش‌آفرینی',
		'elder scrolls'          => 'نقش‌آفرینی',
		'mass effect'            => 'نقش‌آفرینی',
		'baldur\'s gate'          => 'نقش‌آفرینی',
		'baldurs gate'           => 'نقش‌آفرینی',
		'diablo iv'              => 'نقش‌آفرینی',
		'diablo 4'               => 'نقش‌آفرینی',
		'diablo iii'             => 'نقش‌آفرینی',
		'diablo 3'               => 'نقش‌آفرینی',
		'path of exile'          => 'نقش‌آفرینی',
		'monster hunter world'   => 'نقش‌آفرینی',
		'monster hunter rise'    => 'نقش‌آفرینی',
		'monster hunter wilds'   => 'نقش‌آفرینی',
		'pokemon'                => 'نقش‌آفرینی',
		'pokémon'                => 'نقش‌آفرینی',
		'kingdom hearts'         => 'نقش‌آفرینی',
		'tales of'               => 'نقش‌آفرینی',
		'xenoblade'              => 'نقش‌آفرینی',
		'fire emblem'            => 'نقش‌آفرینی',
		'octopath traveler'      => 'نقش‌آفرینی',
		'like a dragon'          => 'نقش‌آفرینی',
		'yakuza'                 => 'نقش‌آفرینی',
		'star ocean'             => 'نقش‌آفرینی',
		'ni no kuni'             => 'نقش‌آفرینی',
		'dark souls'             => 'نقش‌آفرینی',
		'demon\'s souls'          => 'نقش‌آفرینی',
		'demons souls'           => 'نقش‌آفرینی',
		'sekiro'                 => 'نقش‌آفرینی',
		'lies of p'              => 'نقش‌آفرینی',
		'black myth wukong'      => 'نقش‌آفرینی',

		// Adventure
		'life is strange'        => 'ماجراجویی',
		'detroit become human'   => 'ماجراجویی',
		'heavy rain'             => 'ماجراجویی',
		'beyond two souls'       => 'ماجراجویی',
		'walking dead'           => 'ماجراجویی',
		'wolf among us'          => 'ماجراجویی',
		'it takes two'           => 'ماجراجویی',
		'a way out'              => 'ماجراجویی',
		'stray'                  => 'ماجراجویی',
		'gris'                   => 'ماجراجویی',
		'firewatch'              => 'ماجراجویی',
		'what remains of edith finch' => 'ماجراجویی',
		'outer wilds'            => 'ماجراجویی',
		'journey'                => 'ماجراجویی',
		'night in the woods'     => 'ماجراجویی',

		// Family / casual
		'lego '                   => 'خانوادگی',
		'minecraft'              => 'خانوادگی',
		'stardew valley'         => 'خانوادگی',
		'animal crossing'        => 'خانوادگی',
		'overcooked'             => 'خانوادگی',
		'moving out'             => 'خانوادگی',
		'party animals'          => 'خانوادگی',
		'fall guys'              => 'خانوادگی',
		'among us'               => 'خانوادگی',
		'plants vs zombies'      => 'خانوادگی',
		'crazy taxi'             => 'خانوادگی',
		'goat simulator'         => 'خانوادگی',
		'go vacation'            => 'خانوادگی',
		'clubhouse games'        => 'خانوادگی',
		'1-2-switch'             => 'خانوادگی',
		'big brain academy'      => 'خانوادگی',
		'ring fit adventure'     => 'ورزشی',

		// Action / action-adventure blockbusters
		'god of war'             => 'اکشن',
		'uncharted'              => 'اکشن',
		'tomb raider'            => 'اکشن',
		'devil may cry'          => 'اکشن',
		'bayonetta'              => 'اکشن',
		'metal gear solid'       => 'اکشن',
		'spiderman'              => 'اکشن',
		'spider-man'             => 'اکشن',
		'spider man'             => 'اکشن',
		'ratchet'                => 'اکشن',
		'infamous'               => 'اکشن',
		'prototype'              => 'اکشن',
		'sleeping dogs'          => 'اکشن',
		'saints row'             => 'اکشن',
		'just cause'             => 'اکشن',
		'mad max'                => 'اکشن',
		'batman arkham'          => 'اکشن',
		'guardians of the galaxy' => 'اکشن',
		'deadpool'               => 'اکشن',
		'army of two'            => 'اکشن',
		'payday'                 => 'اکشن',
		'control'                => 'اکشن',
		'atomic heart'           => 'اکشن',
		'deathloop'              => 'اکشن',
		'wolfenstein'            => 'اکشن',
		'ryse'                   => 'اکشن',
		'quantum break'          => 'اکشن',
		'remnant'                => 'اکشن',
		'warhammer 40'           => 'اکشن',
		'armored core'           => 'اکشن',
		'stellar blade'          => 'اکشن',
		'rise of the ronin'      => 'اکشن',
		'final fantasy vii rebirth' => 'نقش‌آفرینی',
		'final fantasy xvi'      => 'اکشن',
		'final fantasy 16'       => 'اکشن',
		'ff16'                   => 'اکشن',
		'ff7 rebirth'            => 'نقش‌آفرینی',
		'last of us'             => 'اکشن',
		'days gone'              => 'اکشن',
		'ghostwire'              => 'اکشن',
		'hi-fi rush'             => 'اکشن',
		'plague tale'            => 'اکشن',
		'immortals fenyx'        => 'اکشن',
		'immortals fenix'        => 'اکشن',

		// Additional catalog titles (Persian store naming).
		'naruto'                 => 'مبارزه‌ای',
		'ultimate ninja storm'   => 'مبارزه‌ای',
		'doom'                   => 'تیراندازی',
		'fc 24'                  => 'ورزشی',
		'fc 25'                  => 'ورزشی',
		'fc 26'                  => 'ورزشی',
		'f1 24'                  => 'مسابقه‌ای',
		'f1 25'                  => 'مسابقه‌ای',
		'f1 manager'             => 'ورزشی',
		'assassin\'s creed'       => 'جهان باز',
		'assassins creed'        => 'جهان باز',
		'borderlands 3'          => 'تیراندازی',
		'borderlands 4'          => 'تیراندازی',
		'death stranding'        => 'اکشن',
		'alan wake'              => 'ترسناک',
		'dragon age'             => 'نقش‌آفرینی',
		'star wars jedi'         => 'اکشن',
		'kingdom come'           => 'نقش‌آفرینی',
		'sifu'                   => 'مبارزه‌ای',
		'hades'                  => 'اکشن',
		'wreckfest'              => 'مسابقه‌ای',
		'gang beasts'            => 'خانوادگی',
		'five nights at freddy'  => 'ترسناک',
		'luigi\'s mansion'        => 'ماجراجویی',
		'luigis mansion'         => 'ماجراجویی',
		'hogwarts legacy'        => 'نقش‌آفرینی',
		'project cars'           => 'مسابقه‌ای',
		'paw patrol'             => 'خانوادگی',
		'spongebob'              => 'خانوادگی',
		'باب اسفنجی'             => 'خانوادگی',
		'teenage mutant ninja'   => 'مبارزه‌ای',
		'one piece'              => 'نقش‌آفرینی',
		'mafia'                  => 'اکشن',
		'forspoken'              => 'اکشن',
		'lost judgment'          => 'ماجراجویی',
		'judgment'               => 'ماجراجویی',
		'topspin'                => 'ورزشی',
		'wrc '                   => 'مسابقه‌ای',
		'riders republic'        => 'مسابقه‌ای',
		'back 4 blood'           => 'تیراندازی',
		'hell let loose'         => 'تیراندازی',
		'ready or not'           => 'تیراندازی',
		'robocop'                => 'تیراندازی',
		'indiana jones'          => 'اکشن',
		'dynasty warriors'       => 'اکشن',
		'fatal fury'             => 'مبارزه‌ای',
		'grid legends'           => 'مسابقه‌ای',
		'detroit'                => 'ماجراجویی',
		'knack'                  => 'خانوادگی',
		'dead island'            => 'اکشن',
		'biomutant'              => 'نقش‌آفرینی',
		'dragon ball z'          => 'مبارزه‌ای',
		'dragon ball xenoverse'  => 'مبارزه‌ای',
		'stalker'                => 'بقا',
		's.t.a.l.k.e.r'          => 'بقا',
		'ninja gaiden'           => 'اکشن',
		'split fiction'          => 'ماجراجویی',
		'kena'                   => 'ماجراجویی',
		'the crew motorfest'     => 'مسابقه‌ای',
		'killing floor'          => 'تیراندازی',
		'mount & blade'          => 'استراتژی',
		'anno '                  => 'استراتژی',
		'crew '                  => 'مسابقه‌ای',
		'monster energy supercross' => 'مسابقه‌ای',
		'shinobi'                => 'اکشن',
		'avengers'               => 'اکشن',
		'banishers'              => 'اکشن',
		'blasphemous'            => 'اکشن',
		'ghostbusters'           => 'اکشن',
		'immortals of aveum'     => 'اکشن',
		'wanted: dead'           => 'اکشن',
		'terminator'             => 'تیراندازی',
		'shadow warrior'         => 'تیراندازی',
		'midnight suns'          => 'استراتژی',
		'destroy all humans'     => 'اکشن',
		'fast & furious'         => 'مسابقه‌ای',
		'fast and furious'       => 'مسابقه‌ای',
		'asphalt'                => 'مسابقه‌ای',
		'monster jam'            => 'مسابقه‌ای',
		'smurfs kart'            => 'مسابقه‌ای',
		'animal kart'            => 'مسابقه‌ای',
		'dreamworks all-star kart' => 'مسابقه‌ای',
		'mx vs atv'              => 'مسابقه‌ای',
		'redout'                 => 'مسابقه‌ای',
		'air conflicts'          => 'اکشن',
		'legendary fishing'      => 'شبیه‌سازی',
		'fishing'                => 'شبیه‌سازی',
		'hidden objects'         => 'پازل',
		'plateup'                => 'خانوادگی',
		'overcooked'             => 'خانوادگی',
		'party challenge'        => 'خانوادگی',
		'sports party'           => 'ورزشی',
		'badminton'              => 'ورزشی',
		'rugby'                  => 'ورزشی',
		'jumanji'                => 'ماجراجویی',
		'ben 10'                 => 'خانوادگی',
		'addams family'          => 'خانوادگی',
		'suicide squad'          => 'اکشن',
		'vampire'                => 'نقش‌آفرینی',
		'expedition 33'          => 'نقش‌آفرینی',
		'clair obscur'           => 'نقش‌آفرینی',
		'wuchang'                => 'اکشن',
		'atomfall'               => 'اکشن',
		'choo-choo charles'      => 'ترسناک',
		'high on life'           => 'تیراندازی',
		'core keeper'            => 'بقا',
		'tribes of midgard'      => 'بقا',
		'expeditions'            => 'شبیه‌سازی',
		'mudrunner'              => 'شبیه‌سازی',
		'metro simulator'        => 'شبیه‌سازی',
		'cher nobylite'          => 'بقا',
		'chernobylite'           => 'بقا',
		'weird west'             => 'اکشن',
		'lost records'           => 'ماجراجویی',
		'life is strange'        => 'ماجراجویی',
		'little friends'         => 'خانوادگی',
		'rise of new champions'  => 'ورزشی',
		'power trip'             => 'ماجراجویی',
		'into the dead'          => 'ترسناک',
		'rematch'                => 'ورزشی',
		'lumo'                   => 'پازل',
		'wizardry'               => 'نقش‌آفرینی',
		'granblue fantasy'       => 'نقش‌آفرینی',
		'fantasian'              => 'نقش‌آفرینی',
		'octopath'               => 'نقش‌آفرینی',
		'fire emblem'            => 'استراتژی',
		'warhammer'              => 'استراتژی',
		'class of heroes'        => 'نقش‌آفرینی',
		'conscript'              => 'اکشن',
		'painkiller'             => 'تیراندازی',
		'hell is us'             => 'اکشن',
		'enotria'                => 'اکشن',
		'thymesia'               => 'اکشن',
		'sker ritual'            => 'ترسناک',
		'killer frequency'       => 'ترسناک',
		'still wakes the deep'   => 'ترسناک',
		'ad infinitum'           => 'ترسناک',
		'operation wolf'         => 'تیراندازی',
		'crossfire'              => 'تیراندازی',
		'armored core'           => 'اکشن',
		'daemon x machina'       => 'اکشن',
		'mech'                   => 'اکشن',
		'souls'                  => 'نقش‌آفرینی',
		'elden ring'             => 'جهان باز',
		'lies of p'              => 'نقش‌آفرینی',
		'wo long'                => 'اکشن',
		'wo long'                => 'اکشن',
		'rise of the ronin'      => 'اکشن',
		'stellar blade'          => 'اکشن',
		'first berserker'        => 'اکشن',
		'black myth'             => 'نقش‌آفرینی',
	);
}

/**
 * Fallback keyword rules applied when no title map entry matches.
 *
 * @return array<int, array{keywords: array<int, string>, genre: string}>
 */
function diako_game_genre_keyword_rules(): array {
	return array(
		array(
			'keywords' => array( 'fifa', 'pes', 'efootball', 'nba', 'nhl', 'mlb', 'wwe', 'ufc', 'football', 'soccer', 'tennis', 'golf', 'sport', 'ورزش' ),
			'genre'    => 'ورزشی',
		),
		array(
			'keywords' => array( 'racing', 'forza', 'turismo', 'motorsport', 'nfs', 'drive', 'rally', 'مسابقه', 'formula', 'f1 ' ),
			'genre'    => 'مسابقه‌ای',
		),
		array(
			'keywords' => array( 'fighter', 'tekken', 'mortal kombat', 'brawl', 'smash', 'مبارزه' ),
			'genre'    => 'مبارزه‌ای',
		),
		array(
			'keywords' => array( 'shooter', 'fps', 'tps', 'warfare', 'battlefield', 'duty', 'sniper', 'تیراندازی', 'shoot' ),
			'genre'    => 'تیراندازی',
		),
		array(
			'keywords' => array( 'horror', 'evil', 'nightmare', 'zombie', 'ترسناک', 'survival horror' ),
			'genre'    => 'ترسناک',
		),
		array(
			'keywords' => array( 'strategy', 'civilization', 'tactics', 'rts', 'turn-based', 'استراتژی' ),
			'genre'    => 'استراتژی',
		),
		array(
			'keywords' => array( 'simulator', 'simulation', 'truck', 'farming', 'flight', 'شبیه‌سازی' ),
			'genre'    => 'شبیه‌سازی',
		),
		array(
			'keywords' => array( 'puzzle', 'tetris', 'portal', 'brain', 'پازل' ),
			'genre'    => 'پازل',
		),
		array(
			'keywords' => array( 'dance', 'rhythm', 'guitar hero', 'beat', 'ریتم' ),
			'genre'    => 'ریتم',
		),
		array(
			'keywords' => array( 'platform', 'mario', 'sonic', 'crash', 'kirby', 'پلتفرم' ),
			'genre'    => 'پلتفرمر',
		),
		array(
			'keywords' => array( 'survival', 'survivor', 'rust', 'ark ', 'بقا' ),
			'genre'    => 'بقا',
		),
		array(
			'keywords' => array( 'open world', 'gta', 'red dead', 'skyrim', 'fallout', 'جهان باز' ),
			'genre'    => 'جهان باز',
		),
		array(
			'keywords' => array( 'rpg', 'role playing', 'final fantasy', 'dragon quest', 'witcher', 'souls', 'نقش' ),
			'genre'    => 'نقش‌آفرینی',
		),
		array(
			'keywords' => array( 'adventure', 'quest', 'story', 'life is', 'ماجراجویی' ),
			'genre'    => 'ماجراجویی',
		),
		array(
			'keywords' => array( 'lego', 'minecraft', 'family', 'kids', 'party', 'خانواده', 'کودک' ),
			'genre'    => 'خانوادگی',
		),
		array(
			'keywords' => array( 'action', 'combat', 'battle', 'اکشن' ),
			'genre'    => 'اکشن',
		),
	);
}

/**
 * Convert Persian/Arabic digits to ASCII.
 *
 * @param string $text Input text.
 * @return string
 */
function diako_normalize_digits( string $text ): string {
	$map = array(
		'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
		'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
		'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
		'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
	);

	return strtr( $text, $map );
}

/**
 * Decode HTML entities in product titles.
 *
 * @param string $title Product title.
 * @return string
 */
function diako_decode_game_title( string $title ): string {
	if ( function_exists( 'wp_specialchars_decode' ) ) {
		$title = wp_specialchars_decode( $title );
	}

	return html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Whether a normalized title contains a keyword (word-safe for short tokens).
 *
 * @param string $title   Normalized title.
 * @param string $keyword Keyword or phrase.
 * @return bool
 */
function diako_title_contains_keyword( string $title, string $keyword ): bool {
	$keyword = mb_strtolower( trim( $keyword ), 'UTF-8' );
	if ( '' === $keyword ) {
		return false;
	}

	if ( str_contains( $keyword, ' ' ) || mb_strlen( $keyword, 'UTF-8' ) >= 4 ) {
		return false !== mb_stripos( $title, $keyword, 0, 'UTF-8' );
	}

	$pattern = '/(?<![\p{L}\p{N}])' . preg_quote( $keyword, '/' ) . '(?![\p{L}\p{N}])/iu';

	return 1 === preg_match( $pattern, $title );
}

/**
 * Normalize a product title for matching.
 *
 * @param string $title Product title.
 * @return string
 */
function diako_normalize_game_title( string $title ): string {
	$title = diako_decode_game_title( $title );
	$title = diako_normalize_digits( $title );
	$title = preg_replace( '/\s+/u', ' ', trim( $title ) );

	// Extract the game name from Persian catalog titles: "بازی X برای Platform".
	if ( preg_match( '/^بازی\s+(.+?)\s+برای\s+/u', $title, $matches ) ) {
		$title = trim( $matches[1] );
	} else {
		$title = preg_replace( '/^بازی\s+/u', '', $title );
		$title = preg_replace( '/^ازی\s+/u', '', $title );
		$title = preg_replace( '/\s+برای\s+.*$/u', '', $title );
	}

	$title = mb_strtolower( (string) $title, 'UTF-8' );

	// Strip edition / platform suffixes.
	$patterns = array(
		'/\s*[-–—|]\s*(ps5|ps4|ps3|xbox|switch|pc|nintendo|playstation\s*[345]?|ps vr2?)\b.*$/iu',
		'/\s*\((ps5|ps4|ps3|xbox|switch|pc|region\s*\d+|r\d+)\)\s*$/iu',
		'/\s+(ps5|ps4|ps3|xbox series [xs]|xbox one|switch|pc|ps vr2?)\s*$/iu',
		'/\s+(نسخه|edition|deluxe|ultimate|remastered|definitive|goty|day one|collector|special).*$/iu',
	);

	foreach ( $patterns as $pattern ) {
		$title = preg_replace( $pattern, '', $title );
	}

	$title = str_replace( array( '’', '‘', '`' ), "'", $title );

	return trim( (string) $title );
}

/**
 * Detect Persian genre from a product title.
 *
 * @param string $title Product title.
 * @return string Genre name in Persian.
 */
function diako_detect_game_genre_from_title( string $title ): string {
	$normalized = diako_normalize_game_title( $title );

	if ( '' === $normalized ) {
		return 'متفرقه';
	}

	foreach ( diako_game_genre_title_map() as $needle => $genre ) {
		if ( false !== mb_stripos( $normalized, $needle, 0, 'UTF-8' ) ) {
			return $genre;
		}
	}

	foreach ( diako_game_genre_keyword_rules() as $rule ) {
		foreach ( $rule['keywords'] as $keyword ) {
			if ( diako_title_contains_keyword( $normalized, $keyword ) ) {
				return $rule['genre'];
			}
		}
	}

	return 'متفرقه';
}
