<?php


namespace App\Modules\Contractors\src\Mail;


use App\Modules\Contractors\src\Models\Contract;
use App\Modules\Contractors\src\Models\Contractor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class ContractorLegalMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Contractor
     */
    private $mail;

    /**
     * @var Contract
     */
    private $contract;

    /**
     * Create a new job instance.
     *
     * @param Contractor $user
     * @param Contract $contract
     */
    public function __construct(Contractor $user, Contract $contract)
    {
        $this->mail = $user;
        $this->contract = $contract;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $created_at = now()->format('Y-m-d H:i:s');
        $id = isset( $this->mail->id ) ? $this->mail->id : '';
        $document = isset( $this->mail->document ) ? $this->mail->document : '';
        $first = isset( $this->mail->name ) ? $this->mail->name : '';
        $second = isset( $this->mail->surname ) ? $this->mail->surname : '';
        $name = "$first $second";
        $number = isset($this->contract->contract) ? $this->contract->contract : '000';
        $type = isset($this->contract->contract_type->name) ? $this->contract->contract_type->name : '';
        $file = $this->contract->files()->where('file_type_id', 1)->latest()->first();
        $user = isset($file->user->full_name) ? $file->user->full_name : 'SYSTEM';

        $subject = "{$name} / {$type} / {$number}";

        $path = env('APP_ENV') == 'local'
            ? env('APP_PATH_DEV')
            : env('APP_PATH_PROD');

        return $this->view('mail.mail')
            ->subject($subject)
            ->with([
                'header'    => 'IDRD',
                'title'     => 'Registro Portal Contratista',
                'content'   =>  "El usuario {$user} ha generado el certificado ARL para {$name}.",
                'details'   =>  "
                        <p>Nº Documento: {$document}</p>
                        <p>Nº Contrato: {$number}</p>
                        <p>Tipo de Trámite: {$type}</p>
                        <p>Fecha de Actualización: {$created_at}</p>
                        ",
                // 'hide_btn'  => true,
                'url'       =>  "https://sim.idrd.gov.co/{$path}/es/user/{$id}/contractor",
                'info'      =>  "Ingrese al Portal para continuar con el trámite.",
                'year'      =>  Carbon::now()->year
            ]);
    }
}
