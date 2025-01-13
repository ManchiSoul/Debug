(function($) {

	'use strict';

	// Translate date to English for JavaScript validation
	$.WS_Form.prototype.field_date_translate = function(date_string) {

		// Check date_string is a string
		if(typeof(date_string) !== 'string') { return date_string; }

		// Get translations for current locale
		var translations = this.field_date_translations();
		if(translations === false) { return date_string; }

		// Convert date_string to lowercase
		date_string = date_string.toLowerCase();

		// Get months in English
		var months_english = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

		// Translate - Months - Long
		if(translations.m) {

			date_string = this.field_date_translate_replace(date_string, translations.m, months_english, 12);
		}

		// Translate - Months - Short
		if(translations.n) {

			date_string = this.field_date_translate_replace(date_string, translations.n, months_english, 12);
		}

		// Get days in English
		var days_english = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

		// Translate - Days - Long
		if(translations.b) {

			date_string = this.field_date_translate_replace(date_string, translations.b, days_english, 7);
		}

		// Translate - Days - Short
		if(translations.a) {

			date_string = this.field_date_translate_replace(date_string, translations.a, days_english, 7);
		}

		return date_string;
	}

	$.WS_Form.prototype.field_date_translate_replace = function(date_string, lookups, replacements, count) {

		if(
			(typeof(lookups) !== 'object') ||
			(typeof(replacements) !== 'object')
		) {
			return date_string;
		}

		var date_string_original = date_string;

		// Run through each lookup
		for(var index = 0; index < count; index++) {

			var lookup_value = lookups[index];

			if(typeof(lookup_value) === 'object') {

				// If multiple lookup values exist, process each
				for(var lookup_value_index in lookup_value) {

					if(!lookup_value.hasOwnProperty(lookup_value_index)) { continue; }

					var lookup_value_single = lookup_value[lookup_value_index];

					date_string = date_string.replace(lookup_value_single.toLowerCase(), replacements[index]);
				}

			} else {

				// Straight string swap
				date_string = date_string.replace(lookup_value.toLowerCase(), replacements[index]);
			}

			// There should only be one replacements in a date string, so return if a change occurs
			if(date_string != date_string_original) { return date_string; }
		}

		return date_string;
	}

	// Get translations
	$.WS_Form.prototype.field_date_translations = function() {

		// Get locale
		var locale = ws_form_settings.locale;

		// Get language
		var language = locale.slice(0, 2);

		// Translation lookups
		var translations = {

			ar: { // Arabic
				m: ['كانون الثاني','شباط','آذار','نيسان','مايو','حزيران','تموز','آب','أيلول','تشرين الأول','تشرين الثاني','كانون الأول'],
				a: ['ن','ث','ع','خ','ج','س','ح'],
				b: ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت','الأحد']
			},
			ro: { // Romanian
				m: ['Ianuarie','Februarie','Martie','Aprilie','Mai','Iunie','Iulie','August','Septembrie','Octombrie','Noiembrie','Decembrie'],
				a: ['Du','Lu','Ma','Mi','Jo','Vi','Sâ'],
				b: ['Duminică','Luni','Marţi','Miercuri','Joi','Vineri','Sâmbătă']
			},
			id: { // Indonesian
				m: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
				a: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
				b: ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']
			},
			is: { // Icelandic
				m: ['Janúar','Febrúar','Mars','Apríl','Maí','Júní','Júlí','Ágúst','September','Október','Nóvember','Desember'],
				a: ['Sun','Mán','Þrið','Mið','Fim','Fös','Lau'],
				b: ['Sunnudagur','Mánudagur','Þriðjudagur','Miðvikudagur','Fimmtudagur','Föstudagur','Laugardagur']
			},
			bg: { // Bulgarian
				m: ['Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември'],
				a: ['Нд','Пн','Вт','Ср','Чт','Пт','Сб'],
				b: ['Неделя','Понеделник','Вторник','Сряда','Четвъртък','Петък','Събота']
			},
			fa: { // Persian/Farsi
				m: ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'],
				a: ['یکشنبه','دوشنبه','سه شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'],
				b: ['یک‌شنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنج‌شنبه','جمعه','شنبه','یک‌شنبه']
			},
			ru: { // Russian
				m: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
				a: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
				b: ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота']
			},
			uk: { // Ukrainian
				m: ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'],
				a: ['Ндл','Пнд','Втр','Срд','Чтв','Птн','Сбт'],
				b: ['Неділя','Понеділок','Вівторок','Середа','Четвер','П\'ятниця','Субота']
			},
			en: { // English
				m: ['January','February','March','April','May','June','July','August','September','October','November','December'],
				a: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
				b: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']
			},
			el: { // Ελληνικά
				m: ['Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'],
				a: ['Κυρ','Δευ','Τρι','Τετ','Πεμ','Παρ','Σαβ'],
				b: ['Κυριακή','Δευτέρα','Τρίτη','Τετάρτη','Πέμπτη','Παρασκευή','Σάββατο']
			},
			de: { // German
				m: ['Januar','Februar',['März','Marz'],'April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
				n: [['Jan','Jän'],'Feb',['März','Marz'],'Apr','Mai','Juni','Juli','Aug','Sept','Okt','Nov','Dez'],
				a: ['So','Mo','Di','Mi','Do','Fr','Sa'],
				b: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag']
			},
			nl: { // Dutch
				m: ['januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'],
				n: ['jan','feb','maart','apr','mei','juni','juli','aug','sept',['oct','okt'],'nov','dec'],
				a: ['zo','ma','di','wo','do','vr','za'],
				b: ['zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag']
			},
			tr: { // Turkish
				m: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
				a: ['Paz','Pts','Sal','Çar','Per','Cum','Cts'],
				b: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi']
			},
			fr: { //French
				m: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet',['Août','Aout'],'Septembre','Octobre','Novembre','Décembre'],
				n: ['janv','févr','mars','avril','mai','juin','juil',['août','aout'],'sept','oct','nov',['dec','déc']],
				a: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
				b: ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi']
			},
			es: { // Spanish
				m: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
				n: ['enero','feb','marzo','abr','mayo','jun','jul','agosto',['sept','set'],'oct','nov','dic'],
				a: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
				b: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']
			},
			th: { // Thai
				m: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
				a: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
				b: ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัส','ศุกร์','เสาร์','อาทิตย์']
			},
			pl: { // Polish
				m: ['styczeń','luty','marzec','kwiecień','maj','czerwiec','lipiec','sierpień','wrzesień','październik','listopad','grudzień'],
				n: ['stycz','luty','mar','kwiec','maj','czerw','lip','sierp','wrzes','pazdz','listop','grudz'],
				a: ['nd','pn','wt','śr','cz','pt','sb'],
				b: ['niedziela','poniedziałek','wtorek','środa','czwartek','piątek','sobota']
			},
			pt: { // Portuguese
				m: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
				n: ['jan','fev','março','abril','maio','junho','julho','agosto','set','out','nov','dez'],
				a: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
				b: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado']
			},
			ch: { // Simplified Chinese
				m: ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
				a: ['日','一','二','三','四','五','六']
			},
			se: { // Swedish
				m: ['Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September',  'Oktober','November','December'],
				n: ['jan','febr','mars','april','maj','juni','juli','aug','sept','okt','nov','dec'],
				a: ['Sön','Mån','Tis','Ons','Tor','Fre','Lör']
			},
			km: { // Khmer (ភាសាខ្មែរ)
				m: ['មករា​','កុម្ភៈ','មិនា​','មេសា​','ឧសភា​','មិថុនា​','កក្កដា​','សីហា​','កញ្ញា​','តុលា​','វិច្ឆិកា','ធ្នូ​'],
				a: ['អាទិ​','ច័ន្ទ​','អង្គារ​','ពុធ​','ព្រហ​​','សុក្រ​','សៅរ៍'],
				b: ['អាទិត្យ​','ច័ន្ទ​','អង្គារ​','ពុធ​','ព្រហស្បតិ៍​','សុក្រ​','សៅរ៍']
			},
			kr: { // Korean
				m: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
				a: ['일','월','화','수','목','금','토'],
				b: ['일요일','월요일','화요일','수요일','목요일','금요일','토요일']
			},
			it: { // Italian
				m: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
				n: ['genn','febbr','mar','apr','magg','giugno','luglio','ag','sett','ott','nov','dic'],
				a: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
				b: ['Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato']
			},
			da: { // Dansk
				m: ['Januar','Februar','Marts','April','Maj','Juni','Juli','August','September','Oktober','November','December'],
				n: ['jan','febr','marts','april','maj','juni','juli','aug','sept','okt','nov','dec'],
				a: ['Søn','Man','Tir','Ons','Tor','Fre','Lør'],
				b: ['søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag']
			},
			no: { // Norwegian
				m: ['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'],
				n: ['jan','febr','mars','april','mai','juni','juli','aug','sept','okt','nov','des'],
				a: ['Søn','Man','Tir','Ons','Tor','Fre','Lør'],
				b: ['Søndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','Lørdag']
			},
			ja: { // Japanese
				m: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
				a: ['日','月','火','水','木','金','土'],
				b: ['日曜','月曜','火曜','水曜','木曜','金曜','土曜']
			},
			vi: { // Vietnamese
				m: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
				a: ['CN','T2','T3','T4','T5','T6','T7'],
				b: ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy']
			},
			sl: { // Slovenščina
				m: ['Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'],
				a: ['Ned','Pon','Tor','Sre','Čet','Pet','Sob'],
				b: ['Nedelja','Ponedeljek','Torek','Sreda','Četrtek','Petek','Sobota']
			},
			cs: { // Čeština
				m: ['Leden','Únor','Březen','Duben','Květen','Červen','Červenec','Srpen','Září','Říjen','Listopad','Prosinec'],
				a: ['Ne','Po','Út','St','Čt','Pá','So']
			},
			hu: { // Hungarian
				m: ['Január','Február','Március','Április','Május','Június','Július','Augusztus','Szeptember','Október','November','December'],
				a: ['Va','Hé','Ke','Sze','Cs','Pé','Szo'],
				b: ['vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat']
			},
			az: { //Azerbaijanian (Azeri)
				m: ['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'],
				a: ['B','Be','Ça','Ç','Ca','C','Ş'],
				b: ['Bazar','Bazar ertəsi','Çərşənbə axşamı','Çərşənbə','Cümə axşamı','Cümə','Şənbə']
			},
			bs: { //Bosanski
				m: ['Januar','Februar','Mart','April','Maj','Jun','Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'],
				a: ['Ned','Pon','Uto','Sri','Čet','Pet','Sub'],
				b: ['Nedjelja','Ponedjeljak','Utorak','Srijeda','Četvrtak','Petak','Subota']
			},
			ca: { //Català
				m: ['Gener','Febrer','Març','Abril','Maig','Juny','Juliol','Agost','Setembre','Octubre','Novembre','Desembre'],
				a: ['Dg','Dl','Dt','Dc','Dj','Dv','Ds'],
				b: ['Diumenge','Dilluns','Dimarts','Dimecres','Dijous','Divendres','Dissabte']
			},
			et: { //'Eesti'
				m: ['Jaanuar','Veebruar','Märts','Aprill','Mai','Juuni','Juuli','August','September','Oktoober','November','Detsember'],
				a: ['P','E','T','K','N','R','L'],
				b: ['Pühapäev','Esmaspäev','Teisipäev','Kolmapäev','Neljapäev','Reede','Laupäev']
			},
			eu: { //Euskara
				m: ['Urtarrila','Otsaila','Martxoa','Apirila','Maiatza','Ekaina','Uztaila','Abuztua','Iraila','Urria','Azaroa','Abendua'],
				a: ['Ig.','Al.','Ar.','Az.','Og.','Or.','La.'],
				b: ['Igandea','Astelehena','Asteartea','Asteazkena','Osteguna','Ostirala','Larunbata']
			},
			fi: { //Finnish (Suomi)
				m: ['Tammikuu','Helmikuu','Maaliskuu','Huhtikuu','Toukokuu','Kesäkuu','Heinäkuu','Elokuu','Syyskuu','Lokakuu','Marraskuu','Joulukuu'],
				a: ['Su','Ma','Ti','Ke','To','Pe','La'],
				b: ['sunnuntai','maanantai','tiistai','keskiviikko','torstai','perjantai','lauantai']
			},
			gl: { //Galego
				m: ['Xan','Feb','Maz','Abr','Mai','Xun','Xul','Ago','Set','Out','Nov','Dec'],
				a: ['Dom','Lun','Mar','Mer','Xov','Ven','Sab'],
				b: ['Domingo','Luns','Martes','Mércores','Xoves','Venres','Sábado']
			},
			hr: { //Hrvatski
				m: ['Siječanj','Veljača','Ožujak','Travanj','Svibanj','Lipanj','Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'],
				a: ['Ned','Pon','Uto','Sri','Čet','Pet','Sub'],
				b: ['Nedjelja','Ponedjeljak','Utorak','Srijeda','Četvrtak','Petak','Subota']
			},
			ko: { //Korean (한국어)
				m: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
				a: ['일','월','화','수','목','금','토'],
				b: ['일요일','월요일','화요일','수요일','목요일','금요일','토요일']
			},
			lt: { //Lithuanian (lietuvių)
				m: ['Sausio','Vasario','Kovo','Balandžio','Gegužės','Birželio','Liepos','Rugpjūčio','Rugsėjo','Spalio','Lapkričio','Gruodžio'],
				a: ['Sek','Pir','Ant','Tre','Ket','Pen','Šeš'],
				b: ['Sekmadienis','Pirmadienis','Antradienis','Trečiadienis','Ketvirtadienis','Penktadienis','Šeštadienis']
			},
			lv: { //Latvian (Latviešu)
				m: ['Janvāris','Februāris','Marts','Aprīlis ','Maijs','Jūnijs','Jūlijs','Augusts','Septembris','Oktobris','Novembris','Decembris'],
				a: ['Sv','Pr','Ot','Tr','Ct','Pk','St'],
				b: ['Svētdiena','Pirmdiena','Otrdiena','Trešdiena','Ceturtdiena','Piektdiena','Sestdiena']
			},
			mk: { //Macedonian (Македонски)
				m: ['јануари','февруари','март','април','мај','јуни','јули','август','септември','октомври','ноември','декември'],
				a: ['нед','пон','вто','сре','чет','пет','саб'],
				b: ['Недела','Понеделник','Вторник','Среда','Четврток','Петок','Сабота']
			},
			mn: { //Mongolian (Монгол)
				m: ['1-р сар','2-р сар','3-р сар','4-р сар','5-р сар','6-р сар','7-р сар','8-р сар','9-р сар','10-р сар','11-р сар','12-р сар'],
				a: ['Дав','Мяг','Лха','Пүр','Бсн','Бям','Ням'],
				b: ['Даваа','Мягмар','Лхагва','Пүрэв','Баасан','Бямба','Ням']
			},
			'pt_BR': { //Português(Brasil)
				m: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
				a: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
				b: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado']
			},
			sk: { //Slovenčina
				m: ['Január','Február','Marec','Apríl','Máj','Jún','Júl','August','September','Október','November','December'],
				a: ['Ne','Po','Ut','St','Št','Pi','So'],
				b: ['Nedeľa','Pondelok','Utorok','Streda','Štvrtok','Piatok','Sobota']
			},
			sq: { //Albanian (Shqip)
				m: ['Janar','Shkurt','Mars','Prill','Maj','Qershor','Korrik','Gusht','Shtator','Tetor','Nëntor','Dhjetor'],
				a: ['Die','Hën','Mar','Mër','Enj','Pre','Shtu'],
				b: ['E Diel','E Hënë','E Martē','E Mërkurë','E Enjte','E Premte','E Shtunë']
			},
			'sr_YU': { //Serbian (Srpski)
				m: ['Januar','Februar','Mart','April','Maj','Jun','Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'],
				a: ['Ned','Pon','Uto','Sre','čet','Pet','Sub'],
				b: ['Nedelja','Ponedeljak','Utorak','Sreda','Četvrtak','Petak','Subota']
			},
			sr: { //Serbian Cyrillic (Српски)
				m: ['јануар','фебруар','март','април','мај','јун','јул','август','септембар','октобар','новембар','децембар'],
				a: ['нед','пон','уто','сре','чет','пет','суб'],
				b: ['Недеља','Понедељак','Уторак','Среда','Четвртак','Петак','Субота']
			},
			sv: { //Svenska
				m: ['Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September','Oktober','November','December'],
				a: ['Sön','Mån','Tis','Ons','Tor','Fre','Lör'],
				b: ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag']
			},
			'zh_TW': { //Traditional Chinese (繁體中文)
				m: ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
				a: ['日','一','二','三','四','五','六'],
				b: ['星期日','星期一','星期二','星期三','星期四','星期五','星期六']
			},
			zh: { //Simplified Chinese (简体中文)
				m: ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
				a: ['日','一','二','三','四','五','六'],
				b: ['星期日','星期一','星期二','星期三','星期四','星期五','星期六']
			},
			ug:{ // Uyghur(ئۇيغۇرچە)
				m: ['1-ئاي','2-ئاي','3-ئاي','4-ئاي','5-ئاي','6-ئاي','7-ئاي','8-ئاي','9-ئاي','10-ئاي','11-ئاي','12-ئاي'],
				b: ['يەكشەنبە','دۈشەنبە','سەيشەنبە','چارشەنبە','پەيشەنبە','جۈمە','شەنبە']
			},
			he: { //Hebrew (עברית)
				m: ['ינואר','פברואר','מרץ','אפריל','מאי','יוני','יולי','אוגוסט','ספטמבר','אוקטובר','נובמבר','דצמבר'],
				a: ['א\'','ב\'','ג\'','ד\'','ה\'','ו\'','שבת'],
				b: ['ראשון','שני','שלישי','רביעי','חמישי','שישי','שבת','ראשון']
			},
			hy: { // Armenian
				m: ['Հունվար','Փետրվար','Մարտ','Ապրիլ','Մայիս','Հունիս','Հուլիս','Օգոստոս','Սեպտեմբեր','Հոկտեմբեր','Նոյեմբեր','Դեկտեմբեր'],
				a: ['Կի','Երկ','Երք','Չոր','Հնգ','Ուրբ','Շբթ'],
				b: ['Կիրակի','Երկուշաբթի','Երեքշաբթի','Չորեքշաբթի','Հինգշաբթի','Ուրբաթ','Շաբաթ']
			},
			kg: { // Kyrgyz
				m: ['Үчтүн айы','Бирдин айы','Жалган Куран','Чын Куран','Бугу','Кулжа','Теке','Баш Оона','Аяк Оона','Тогуздун айы','Жетинин айы','Бештин айы'],
				a: ['Жек','Дүй','Шей','Шар','Бей','Жум','Ише'],
				b: ['Жекшемб','Дүйшөмб','Шейшемб','Шаршемб','Бейшемби','Жума','Ишенб']
			},
			rm: { // Romansh
				m: ['Schaner','Favrer','Mars','Avrigl','Matg','Zercladur','Fanadur','Avust','Settember','October','November','December'],
				a: ['Du','Gli','Ma','Me','Gie','Ve','So'],
				b: ['Dumengia','Glindesdi','Mardi','Mesemna','Gievgia','Venderdi','Sonda']
			},
			ka: { // Georgian
				m: ['იანვარი','თებერვალი','მარტი','აპრილი','მაისი','ივნისი','ივლისი','აგვისტო','სექტემბერი','ოქტომბერი','ნოემბერი','დეკემბერი'],
				a: ['კვ','ორშ','სამშ','ოთხ','ხუთ','პარ','შაბ'],
				b: ['კვირა','ორშაბათი','სამშაბათი','ოთხშაბათი','ხუთშაბათი','პარასკევი','შაბათი']
			}
		};

		// Attempt to find exact locale
		if(typeof(translations[locale]) === 'object') {

			return translations[locale];
		}

		// Attempt to find by language
		if(typeof(translations[language]) === 'object') {

			return translations[language];
		}

		return false;
	}

})(jQuery);
