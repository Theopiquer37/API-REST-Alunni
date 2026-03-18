<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlunniController
{
  private function db() {
    return new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
  }

  public function index(Request $request, Response $response, $args){
    $db = $this->db();
    $result = $db->query("SELECT * FROM alunni");
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
    $db = $this->db();

    $stmt = $db->prepare("SELECT * FROM alunni WHERE id = ?");
    $stmt->bind_param("i", $args['id']);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }


  public function create(Request $request, Response $response, $args){
    $db = $this->db();

  
    $body = $request->getParsedBody() ?? [];


    $nome = isset($body['nome']) ? $body['nome'] : null;
    $cognome = isset($body['cognome']) ? $body['cognome'] : null;

    if (!$nome || !$cognome) {
      $response->getBody()->write(json_encode(["message" => "Nome e cognome sono richiesti"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    $stmt = $db->prepare("INSERT INTO alunni (nome, cognome) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $cognome);
    $stmt->execute();

    $response->getBody()->write(json_encode([
      "message" => "Creato",
      "id" => $db->insert_id
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(201);
  }


  public function update(Request $request, Response $response, $args){
    $db = $this->db();

    $body = $request->getParsedBody() ?? [];

    $nome = isset($body['nome']) ? $body['nome'] : null;
    $cognome = isset($body['cognome']) ? $body['cognome'] : null;

    if (!$nome || !$cognome) {
      $response->getBody()->write(json_encode(["message" => "Nome e cognome sono richiesti"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    $stmt = $db->prepare("UPDATE alunni SET nome = ?, cognome = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $cognome, $args['id']);
    $stmt->execute();

    $response->getBody()->write(json_encode([
      "message" => "Aggiornato"
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function destroy(Request $request, Response $response, $args){
    $db = $this->db();

    $stmt = $db->prepare("DELETE FROM alunni WHERE id = ?");
    $stmt->bind_param("i", $args['id']);
    $stmt->execute();

    $response->getBody()->write(json_encode([
      "message" => "Eliminato"
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }
}