/////////   usando flow - recebendo informacoes do formulario
            $interactive =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'] ?? null;
            $interactive_type =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
            $interactive_nfm_reply =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
            $interactive_nfm_reply_response_json =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
            $interactive_nfm_reply_body =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
            $interactive_nfm_reply_name =  $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
            $messagesFrom =  $data['entry'][0]['changes'][0]['value']['messages'][0]['from'] ?? null;

            if($interactive){

            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);

            // Atribuindo cada valor a uma variável
            $nome = $data["nome"];
            $dataNascimento = $data["dataNascimento"];
            $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);
            $dataNascimentoInt = $dataNascimentoObj->format('Y-m-d');

            $flow_token = $data["flow_token"];
            $nomePai = $data["nomePai"];
            $nomeMae = $data["nomeMae"];
            $flow_description =$data["description"];
            if($entry_id = '189514994242034')
            {
                $empresaID = 1029;
            }



                $formandobasewhatsapp = formandobasewhatsapp::where('EmpresaID', $empresaID)
                    ->where('nome',$nome)->first();


                if($formandobasewhatsapp)
                {
                    $formandobasewhatsapp->update([
                        'EmpresaID' => $empresaID,
                        'nome' => $nome ?? null,
                        'nascimento' => $dataNascimentoInt ?? null, // Remova um $ extra de $$dataNascimentoInt
                        'flow_token' => $flow_token ?? null,
                        'nomePai' => $nomePai ?? null,
                        'nomeMae' => $nomeMae ?? null,
                        'flow_description' => $flow_description ?? null,
                        'user_atendimento' => Auth::user()->name ?? null,
                        'telefone' => $messagesFrom ?? null,
                    ]);


                    // dd($interactive, $interactive_type, $interactive_nfm_reply, $interactive_nfm_reply_response_json,  $interactive_nfm_reply_body,  $interactive_nfm_reply_name,
                    // $nome, $dataNascimentoObj, $flow_token, $nomePai, $nomeMae, $flow_description, $empresaID, $formandobasewhatsapp);

                }
                else
                {

                    $newformandobasewhatsapp = FormandoBaseWhatsapp::create([
                        'EmpresaID' => $empresaID,
                        'nome' => $nome ?? null,
                        'nascimento' => $dataNascimentoInt ?? null,
                        'flow_token' => $flow_token ?? null,
                        'nomePai' => $nomePai ?? null,
                        'nomeMae' => $nomeMae ?? null,
                        'flow_description' => $flow_description ?? null,
                        'user_atendimento' => Auth::user()->name ?? null,
                        'telefone' => $messagesFrom ?? null,
                    ]);
                    // dd($interactive, $interactive_type, $interactive_nfm_reply, $interactive_nfm_reply_response_json,  $interactive_nfm_reply_body,  $interactive_nfm_reply_name,
                    // $nome, $dataNascimento, $flow_token, $nomePai, $nomeMae, $flow_description, $empresaID, $formandobasewhatsapp, $newformandobasewhatsapp );
                 }



            }

