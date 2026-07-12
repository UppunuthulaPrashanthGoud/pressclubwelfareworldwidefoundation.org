// Complete Indian States and Districts Database
// Updated with latest administrative divisions

var state_arr = new Array(
    "Andaman and Nicobar Islands",
    "Andhra Pradesh",
    "Arunachal Pradesh",
    "Assam",
    "Bihar",
    "Chandigarh",
    "Chhattisgarh",
    "Dadra and Nagar Haveli and Daman and Diu",
    "Delhi",
    "Goa",
    "Gujarat",
    "Haryana",
    "Himachal Pradesh",
    "Jammu and Kashmir",
    "Jharkhand",
    "Karnataka",
    "Kerala",
    "Ladakh",
    "Lakshadweep",
    "Madhya Pradesh",
    "Maharashtra",
    "Manipur",
    "Meghalaya",
    "Mizoram",
    "Nagaland",
    "Odisha",
    "Puducherry",
    "Punjab",
    "Rajasthan",
    "Sikkim",
    "Tamil Nadu",
    "Telangana",
    "Tripura",
    "Uttar Pradesh",
    "Uttarakhand",
    "West Bengal"
);

var s_a = new Array();
s_a[0] = "";

// Andaman and Nicobar Islands
s_a[1] = "Nicobar | North and Middle Andaman | South Andaman";

// Andhra Pradesh
s_a[2] = "Alluri Sitharama Raju | Anakapalli | Anantapur | Annamayya | Bapatla | Chittoor | Dr. B.R. Ambedkar Konaseema | East Godavari | Eluru | Guntur | Kakinada | Krishna | Kurnool | Nandyal | NTR | Palnadu | Parvathipuram Manyam | Prakasam | Srikakulam | Sri Potti Sriramulu Nellore | Sri Sathya Sai | Tirupati | Visakhapatnam | Vizianagaram | West Godavari | YSR";

// Arunachal Pradesh
s_a[3] = "Anjaw | Changlang | Dibang Valley | East Kameng | East Siang | Kamle | Kra Daadi | Kurung Kumey | Lepa Rada | Lohit | Longding | Lower Dibang Valley | Lower Siang | Lower Subansiri | Namsai | Pakke Kessang | Papum Pare | Shi Yomi | Siang | Tawang | Tirap | Upper Siang | Upper Subansiri | West Kameng | West Siang";

// Assam
s_a[4] = "Baksa | Barpeta | Biswanath | Bongaigaon | Cachar | Charaideo | Chirang | Darrang | Dhemaji | Dhubri | Dibrugarh | Dima Hasao | Goalpara | Golaghat | Hailakandi | Hojai | Jorhat | Kamrup | Kamrup Metropolitan | Karbi Anglong | Karimganj | Kokrajhar | Lakhimpur | Majuli | Morigaon | Nagaon | Nalbari | Sivasagar | Sonitpur | South Salmara-Mankachar | Tinsukia | Udalguri | West Karbi Anglong";

// Bihar
s_a[5] = "Araria | Arwal | Aurangabad | Banka | Begusarai | Bhagalpur | Bhojpur | Buxar | Darbhanga | East Champaran | Gaya | Gopalganj | Jamui | Jehanabad | Kaimur | Katihar | Khagaria | Kishanganj | Lakhisarai | Madhepura | Madhubani | Munger | Muzaffarpur | Nalanda | Nawada | Patna | Purnia | Rohtas | Saharsa | Samastipur | Saran | Sheikhpura | Sheohar | Sitamarhi | Siwan | Supaul | Vaishali | West Champaran";

// Chandigarh
s_a[6] = "Chandigarh";

// Chhattisgarh
s_a[7] = "Balod | Baloda Bazar | Balrampur | Bastar | Bemetara | Bijapur | Bilaspur | Dantewada | Dhamtari | Durg | Gariaband | Gaurela Pendra Marwahi | Janjgir Champa | Jashpur | Kabirdham | Kanker | Kondagaon | Korba | Koriya | Mahasamund | Manendragarh Chirmiri Bharatpur | Mohla Manpur | Mungeli | Narayanpur | Raigarh | Raipur | Rajnandgaon | Sakti | Sarangarh Bilaigarh | Sukma | Surajpur | Surguja";

// Dadra and Nagar Haveli and Daman and Diu
s_a[8] = "Dadra and Nagar Haveli | Daman | Diu";

// Delhi
s_a[9] = "Central Delhi | East Delhi | New Delhi | North Delhi | North East Delhi | North West Delhi | Shahdara | South Delhi | South East Delhi | South West Delhi | West Delhi";

// Goa
s_a[10] = "North Goa | South Goa";

// Gujarat
s_a[11] = "Ahmedabad | Amreli | Anand | Aravalli | Banaskantha | Bharuch | Bhavnagar | Botad | Chhota Udaipur | Dahod | Dang | Devbhoomi Dwarka | Gandhinagar | Gir Somnath | Jamnagar | Junagadh | Kheda | Kutch | Mahisagar | Mehsana | Morbi | Narmada | Navsari | Panchmahal | Patan | Porbandar | Rajkot | Sabarkantha | Surat | Surendranagar | Tapi | Vadodara | Valsad";

// Haryana
s_a[12] = "Ambala | Bhiwani | Charkhi Dadri | Faridabad | Fatehabad | Gurugram | Hisar | Jhajjar | Jind | Kaithal | Karnal | Kurukshetra | Mahendragarh | Nuh | Palwal | Panchkula | Panipat | Rewari | Rohtak | Sirsa | Sonipat | Yamunanagar";

// Himachal Pradesh
s_a[13] = "Bilaspur | Chamba | Hamirpur | Kangra | Kinnaur | Kullu | Lahaul and Spiti | Mandi | Shimla | Sirmaur | Solan | Una";

// Jammu and Kashmir
s_a[14] = "Anantnag | Bandipora | Baramulla | Budgam | Doda | Ganderbal | Jammu | Kathua | Kishtwar | Kulgam | Kupwara | Poonch | Pulwama | Rajouri | Ramban | Reasi | Samba | Shopian | Srinagar | Udhampur";

// Jharkhand
s_a[15] = "Bokaro | Chatra | Deoghar | Dhanbad | Dumka | East Singhbhum | Garhwa | Giridih | Godda | Gumla | Hazaribagh | Jamtara | Khunti | Koderma | Latehar | Lohardaga | Pakur | Palamu | Ramgarh | Ranchi | Sahebganj | Seraikela Kharsawan | Simdega | West Singhbhum";

// Karnataka
s_a[16] = "Bagalkote | Ballari | Belagavi | Bengaluru Rural | Bengaluru Urban | Bidar | Chamarajanagar | Chikkaballapur | Chikkamagaluru | Chitradurga | Dakshina Kannada | Davangere | Dharwad | Gadag | Hassan | Haveri | Kalaburagi | Kodagu | Kolar | Koppal | Mandya | Mysuru | Raichur | Ramanagara | Shivamogga | Tumakuru | Udupi | Uttara Kannada | Vijayapura | Yadgir";

// Kerala
s_a[17] = "Alappuzha | Ernakulam | Idukki | Kannur | Kasaragod | Kollam | Kottayam | Kozhikode | Malappuram | Palakkad | Pathanamthitta | Thiruvananthapuram | Thrissur | Wayanad";

// Ladakh
s_a[18] = "Kargil | Leh";

// Lakshadweep
s_a[19] = "Lakshadweep";

// Madhya Pradesh
s_a[20] = "Agar Malwa | Alirajpur | Anuppur | Ashoknagar | Balaghat | Barwani | Betul | Bhind | Bhopal | Burhanpur | Chachaura | Chhatarpur | Chhindwara | Damoh | Datia | Dewas | Dhar | Dindori | Guna | Gwalior | Harda | Hoshangabad | Indore | Jabalpur | Jhabua | Katni | Khandwa | Khargone | Maihar | Mandla | Mandsaur | Morena | Nagda | Narsinghpur | Narmadapuram | Neemuch | Niwari | Pandhurna | Panna | Raisen | Rajgarh | Ratlam | Rewa | Sagar | Satna | Sehore | Seoni | Shahdol | Shajapur | Sheopur | Shivpuri | Sidhi | Singrauli | Tikamgarh | Ujjain | Umaria | Vidisha";

// Maharashtra
s_a[21] = "Ahmednagar | Akola | Amravati | Aurangabad | Beed | Bhandara | Buldhana | Chandrapur | Dhule | Gadchiroli | Gondia | Hingoli | Jalgaon | Jalna | Kolhapur | Latur | Mumbai City | Mumbai Suburban | Nagpur | Nanded | Nandurbar | Nashik | Osmanabad | Palghar | Parbhani | Pune | Raigad | Ratnagiri | Sangli | Satara | Sindhudurg | Solapur | Thane | Wardha | Washim | Yavatmal";

// Manipur
s_a[22] = "Bishnupur | Chandel | Churachandpur | Imphal East | Imphal West | Jiribam | Kakching | Kamjong | Kangpokpi | Noney | Pherzawl | Senapati | Tamenglong | Tengnoupal | Thoubal | Ukhrul";

// Meghalaya
s_a[23] = "East Garo Hills | East Jaintia Hills | East Khasi Hills | Mairang | North Garo Hills | Ri Bhoi | South Garo Hills | South West Garo Hills | South West Khasi Hills | West Garo Hills | West Jaintia Hills | West Khasi Hills";

// Mizoram
s_a[24] = "Aizawl | Champhai | Hnahthial | Khawzawl | Kolasib | Lawngtlai | Lunglei | Mamit | Saiha | Saitual | Serchhip";

// Nagaland
s_a[25] = "Chümoukedima | Dimapur | Kiphire | Kohima | Longleng | Mokokchung | Mon | Niuland | Noklak | Peren | Phek | Shamator | Tseminyü | Tuensang | Wokha | Zunheboto";

// Odisha
s_a[26] = "Angul | Balangir | Balasore | Bargarh | Bhadrak | Boudh | Cuttack | Debagarh | Dhenkanal | Gajapati | Ganjam | Jagatsinghpur | Jajpur | Jharsuguda | Kalahandi | Kandhamal | Kendrapara | Kendujhar | Khordha | Koraput | Malkangiri | Mayurbhanj | Nabarangpur | Nayagarh | Nuapada | Puri | Rayagada | Sambalpur | Subarnapur | Sundargarh";

// Puducherry
s_a[27] = "Karaikal | Mahe | Puducherry | Yanam";

// Punjab
s_a[28] = "Amritsar | Barnala | Bathinda | Faridkot | Fatehgarh Sahib | Fazilka | Ferozepur | Gurdaspur | Hoshiarpur | Jalandhar | Kapurthala | Ludhiana | Mansa | Moga | Muktsar | Pathankot | Patiala | Rupnagar | Sahibzada Ajit Singh Nagar | Sangrur | Shahid Bhagat Singh Nagar | Tarn Taran";

// Rajasthan
s_a[29] = "Ajmer | Alwar | Banswara | Baran | Barmer | Bharatpur | Bhilwara | Bikaner | Bundi | Chittorgarh | Churu | Dausa | Dholpur | Dungarpur | Ganganagar | Hanumangarh | Jaipur | Jaisalmer | Jalore | Jhalawar | Jhunjhunu | Jodhpur | Karauli | Kota | Nagaur | Pali | Pratapgarh | Rajsamand | Sawai Madhopur | Sikar | Sirohi | Tonk | Udaipur";

// Sikkim
s_a[30] = "East Sikkim | North Sikkim | South Sikkim | West Sikkim";

// Tamil Nadu
s_a[31] = "Ariyalur | Chengalpattu | Chennai | Coimbatore | Cuddalore | Dharmapuri | Dindigul | Erode | Kallakurichi | Kanchipuram | Kanyakumari | Karur | Krishnagiri | Madurai | Mayiladuthurai | Nagapattinam | Namakkal | Nilgiris | Perambalur | Pudukkottai | Ramanathapuram | Ranipet | Salem | Sivaganga | Tenkasi | Thanjavur | Theni | Thoothukudi | Tiruchirappalli | Tirunelveli | Tirupattur | Tiruppur | Tiruvallur | Tiruvannamalai | Tiruvarur | Vellore | Viluppuram | Virudhunagar";

// Telangana
s_a[32] = "Adilabad | Bhadradri Kothagudem | Hanamkonda | Hyderabad | Jagtial | Jangaon | Jayashankar Bhupalpally | Jogulamba Gadwal | Kamareddy | Karimnagar | Khammam | Komaram Bheem | Mahabubabad | Mahabubnagar | Mancherial | Medak | Medchal–Malkajgiri | Mulugu | Nagarkurnool | Nalgonda | Narayanpet | Nirmal | Nizamabad | Peddapalli | Rajanna Sircilla | Rangareddy | Sangareddy | Siddipet | Suryapet | Vikarabad | Wanaparthy | Warangal | Yadadri Bhuvanagiri";

// Tripura
s_a[33] = "Dhalai | Gomati | Khowai | North Tripura | Sepahijala | South Tripura | Unakoti | West Tripura";

// Uttar Pradesh
s_a[34] = "Agra | Aligarh | Ambedkar Nagar | Amethi | Amroha | Auraiya | Ayodhya | Azamgarh | Baghpat | Bahraich | Ballia | Balrampur | Banda | Barabanki | Bareilly | Basti | Bhadohi | Bijnor | Budaun | Bulandshahr | Chandauli | Chitrakoot | Deoria | Etah | Etawah | Farrukhabad | Fatehpur | Firozabad | Gautam Buddha Nagar | Ghaziabad | Ghazipur | Gonda | Gorakhpur | Hamirpur | Hapur | Hardoi | Hathras | Jalaun | Jaunpur | Jhansi | Kannauj | Kanpur Dehat | Kanpur Nagar | Kasganj | Kaushambi | Kheri | Kushinagar | Lalitpur | Lucknow | Maharajganj | Mahoba | Mainpuri | Mathura | Mau | Meerut | Mirzapur | Moradabad | Muzaffarnagar | Pilibhit | Pratapgarh | Prayagraj | Raebareli | Rampur | Saharanpur | Sambhal | Sant Kabir Nagar | Shahjahanpur | Shamli | Shravasti | Siddharthnagar | Sitapur | Sonbhadra | Sultanpur | Unnao | Varanasi";

// Uttarakhand
s_a[35] = "Almora | Bageshwar | Chamoli | Champawat | Dehradun | Haridwar | Nainital | Pauri Garhwal | Pithoragarh | Rudraprayag | Tehri Garhwal | Udham Singh Nagar | Uttarkashi";

// West Bengal
s_a[36] = "Alipurduar | Bankura | Birbhum | Cooch Behar | Dakshin Dinajpur | Darjeeling | Hooghly | Howrah | Jalpaiguri | Jhargram | Kalimpong | Kolkata | Malda | Murshidabad | Nadia | North 24 Parganas | Paschim Bardhaman | Paschim Medinipur | Purba Bardhaman | Purba Medinipur | Purulia | South 24 Parganas | Uttar Dinajpur";

// Function to populate state dropdown
function print_state(state_id) {
    var option_str = document.getElementById(state_id);
    option_str.length = 0;
    option_str.options[0] = new Option('Select State', '');
    option_str.selectedIndex = 0;
    for (var i = 0; i < state_arr.length; i++) {
        option_str.options[option_str.length] = new Option(state_arr[i], state_arr[i]);
    }
}

// Function to populate district dropdown based on selected state
function print_city(city_id, city_index) {
    var option_str = document.getElementById(city_id);
    option_str.length = 0;
    option_str.options[0] = new Option('Select District', '');
    option_str.selectedIndex = 0;
    var city_arr = s_a[city_index].split("|");
    for (var i = 0; i < city_arr.length; i++) {
        var district = city_arr[i].trim();
        if (district) {
            option_str.options[option_str.length] = new Option(district, district);
        }
    }
}

// Enhanced function with error handling
function populateDistricts(stateElementId, districtElementId) {
    var stateElement = document.getElementById(stateElementId);
    var districtElement = document.getElementById(districtElementId);
    
    if (!stateElement || !districtElement) {
        console.error('State or District element not found');
        return;
    }
    
    var selectedState = stateElement.value;
    var stateIndex = -1;
    
    // Find the index of selected state
    for (var i = 0; i < state_arr.length; i++) {
        if (state_arr[i] === selectedState) {
            stateIndex = i + 1; // +1 because s_a array starts from index 1
            break;
        }
    }
    
    if (stateIndex > 0) {
        print_city(districtElementId, stateIndex);
    } else {
        districtElement.length = 0;
        districtElement.options[0] = new Option('Select District', '');
    }
}

// Auto-initialize function
function initializeStateCityDropdowns(stateId, cityId) {
    print_state(stateId);
    
    var stateElement = document.getElementById(stateId);
    if (stateElement) {
        stateElement.addEventListener('change', function() {
            populateDistricts(stateId, cityId);
        });
    }
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        state_arr: state_arr,
        s_a: s_a,
        print_state: print_state,
        print_city: print_city,
        populateDistricts: populateDistricts,
        initializeStateCityDropdowns: initializeStateCityDropdowns
    };
}