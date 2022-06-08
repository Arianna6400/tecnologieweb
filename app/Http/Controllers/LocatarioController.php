<?php

namespace App\Http\Controllers;

use Auth;
use App\Http\Requests\NewMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Utenti;
use App\Models\Resource\Alloggio;
use App\Models\Resource\Messaggio;
use App\Models\Resource\Caratteristiche;
use App\Models\Filtri;
use App\Models\Opzionate;
use App\Models\Chat;
use App\Models\Catalogo;
use App\Models\ListaFaq;
use App\Models\OffertaSingola;
use App\Models\OpzionaAlloggio;

class LocatarioController extends Controller
{
    protected $_opzionate;
    protected $_locatario;
    protected $_opziona;
    protected $_chat;
    protected $_offertaSingola;
    protected $_catalogoModel;
    protected $_faqModel;
    protected $alloggi_filtrati;
    protected $filtri;

    public function __construct() {
        $this->middleware('can:isLocatario');
        $this->_opzionate = new Opzionate;
        $this->_opziona = new OpzionaAlloggio;
        $this->filtri = new Filtri;
        $this->_offertaSingola = new OffertaSingola;
        $this->_catalogoModel = new Catalogo;
        $this->_faqModel = new ListaFaq;
        $this->_locatario = new Utenti;
        $this->_locatario->role = 'Locatario';
        $this->_chat = new Chat;
        $this->alloggi_filtrati = collect([]);
        $this->_caratteristiche = new Caratteristiche;
    }

    public function Opziona($idAlloggio){
        echo $this->_opzionate->opzionatoLocatario(Auth::user()->Username);
        echo $this->_opziona->opziona($idAlloggio, Auth::user()->Username);
        //nella view dobbiamo vedere cosa c'è dentro esito, perchè dalla funzione opziona, 
        //se ritorno true ho potuto fare l'inserimento, se ritorno false, o l'alloggio è pieno o ho già opzionato altro --> nella view 
        //se dentro esito c'è false non devo far vedere il tasto opziona
        return redirect('/locatario')
            ->with('disponibilità', $this->_opzionate->disponibili($idAlloggio));
    }
    // fa partire la vista 
    public function index(){
        return view('locatario');
    }

    public function chat(){
        return view('chat')
            ->with('messaggi', $this->_chat->showChat(Auth::user()->Username));
    }
    
    public function newMessage(NewMessageRequest $request){
        $messaggio = new Messaggio;
        $messaggio->Mittente = $request->Mittente;
        $messaggio->Destinatario = $request->Destinatario;
        $messaggio->IdAlloggio = $request->IdAlloggio;
        $messaggio->Data = $request->Data;
        $messaggio->Orario = $request->Orario;
        $messaggio->Contenuto = $request->Contenuto;
        $messaggio->fill($request->validated());
        $messaggio->save();
        
        return redirect('/locatario/chat');
    }
    
    public function formMessaggio($IdMessaggio, $IdAlloggio){
        date_default_timezone_set("Europe/Rome");
        return view('insert/insertMessage')
            ->with('usernameLoggato', Auth::user()->Username)
            //ritorna il destinatario FUTURO del messaggio (ovvero quello che quando vado a clickare su rispondi era il mittente
            ->with('destinatario', $this->_chat->destinatarioByIdMessaggio($IdMessaggio)->Mittente)
            ->with('alloggio', $IdAlloggio)
            ->with('data', date("Y/m/d"))
            ->with('orario', date("H:i"));
    }
    
    //mostra l'alloggio opzionato dal locatario
    public function showMiaOpzionata() {
        return view('opzionate')
            ->with('alloggi_opzionati', $this->_opzionate->opzionatoLocatario(Auth::user()->Username)); 
    }

    //mostralefaq
    public function showFaq() {
        $faq = $this->_faqModel->ritornaFaq();
        return view('FAQ')
               ->with('faq', $faq);
    }

    // mostra catalogo intero
    public function showCatalog(){
        $tutti_alloggi = $this->_catalogoModel->ritornaAlloggi();
        return view('locatario')
               ->with('catalogo_intero', $tutti_alloggi);
    }
    
    // mostra il catalogo filtrato tramite nome della citta e tramite i checkbox 
    public function showByCityandCheckBox(){
        $data = $request->all();
        $filtrati = $this->_catalogoModel->filtraggioIniziale($data['citta'],$data['tipoalloggio']);
        return view('home')
               ->with('filtrati', $filtrati)
               ->with('citta', 'Ancona')
               ->with('tipo', 'Appartamento');
       
    }

    //mostra l'alloggio che viene selezionato cliccando il titolo
    public function showOfferta($id){
        $alloggio = $this->_offertaSingola->findAlloggioID($id);
        //offerta è un elemento singolo
        $offerta = $this->_offertaSingola->getAlloggioSelezionato($alloggio);
        echo $offerta->ID;
        return view('offerta')
             ->with('opzionateDa', $this->_opzionate->opzionate(Auth::user()->Username))
             ->with('offerta', $offerta)
             ->with('esito', $this->_opziona->opziona($offerta->ID, Auth::user()->Username));
    }
    
    public function showAllLocal(){
        return view('catalogolocatario')
        ->with('alloggi', Alloggio::all());
    }
    
    public function showFilteredLocal(Request $request){
        $filtri = $request->all();
        foreach($filtri as $key => $value){
        if($key != '_token'){
            foreach($value as $key1 => $value1){
                   $this->applyFilter($key, $value1);
            }
          }
        }
        return view('catalogolocatario')
        ->with('alloggi', $this->alloggi_filtrati->collapse()->unique());
     }

    private function applyFilter($filtro,$scelta){
        switch($filtro){
        case 'servizi_aggiuntivi': $this->alloggi_filtrati->push($this->filtri->filtroserviziAggiuntivi($scelta));
                                   break;
        case 'numero_locali': $this->alloggi_filtrati->push($this->filtri->filtroNumeroLocali($scelta));
                              break;
        case 'posti_letto_stanza': $this->alloggi_filtrati->push($this->filtri->filtropostiLettoStanza($scelta));
                                   break;
        case 'numero_bagni': $this->alloggi_filtrati->push($this->filtri->filtroNumeroBagni($scelta));
                             break;
        case 'numero_stanze_letto': $this->alloggi_filtrati->push($this->filtri->filtronumeroStanze($scelta));
                                    break;
        case 'eta_minima': $this->alloggi_filtrati->push($this->filtri->filtroEtaMinima($scelta));
                           break;
        case 'sesso_richiesto': $this->alloggi_filtrati->push($this->filtri->filtroSessoRischiesto($scelta));
                                break;
    }
}

    //permette di aprire il profilo utente
    public function showProfile(){
        return view('profilo')->with('utente',auth()->user());
    }
    //permette di modificare il profilo prendendo i dati da una request
    public function updateProfile(){
 
    }
    
    //permette di mostra gli alloggi compresi di filtri presi da una request
    public function showFilteredHouse(){
        
    }
    // permette di ozionare un alloggio, la richiesta di opzione viene sempre gestita da una request
    public function houseOption(){
        
    }
    // permette di visualizzare la chat(permette di mandare un messaggio?)
    public function showChat(){
        
    }
    
     // permette di inviare un messaggio, va passato l'id del mittente e l'id del destinatario
    public function sendMessage(){
        
    }
    
    
}