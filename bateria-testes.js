import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    vus: 10,          // 10 Usuários Virtuais Simultâneos (Requisito do trabalho!)
    duration: '30s',  // Tempo de duração do estresse
};

export default function () {
    const url = 'http://chat-service:8001/api/messages';
    
    const payload = JSON.stringify({
        receiver_id: 2,
        content: `Mensagem de carga distribuída enviada pelo VU nº ${__VU} - iteração ${__ITER}`,
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer 1|token-valido-de-teste',
        },
    };

    const res = http.post(url, payload, params);

    if (res.status !== 201) {
        console.log(`Erro: ${res.status} - Body: ${res.body}`);
    }

    check(res, {
        'status é 201 (Created)': (r) => r.status === 201,
    });

    sleep(0.5); 
}