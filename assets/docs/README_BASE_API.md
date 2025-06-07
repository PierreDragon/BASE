# BASE API 2.1 – Endpoints REST

Ce document décrit l’utilisation des endpoints RESTful disponibles dans le système BASE (aussi connu sous le nom MAPI++).

---

## 📘 Structure d'adresse TLCi

```
/api/get/{id_user}/{token}/t{table}/l{line}/c{column}
```

- `t{table}` → Table ID
- `l{line}` → Ligne (record)
- `c{column}` → Colonne (champ)
- `{id_user}` et `{token}` → Authentification API
- `?raw=true` ou `.raw` → retourne une valeur brute

---

## 🔄 Méthodes supportées

### 🔍 GET – Lire des données

| Exemple URL                                      | Description                              |
|--------------------------------------------------|------------------------------------------|
| `/api/get/2/221712152/t2`                        | Toute la table 2                         |
| `/api/get/2/221712152/t2/l4`                     | Enregistrement complet ligne 4           |
| `/api/get/2/221712152/t2/l4/c3`                  | Valeur cellule ligne 4, colonne 3        |
| `/api/get/2/221712152/t2/l4/c3.raw`              | Valeur brute (texte simple)              |
| `/api/get/2/221712152/t2/where/label/LIKE/Planck`| Requête avec filtre WHERE                |

---

### ➕ POST – Ajouter une ligne

- **Méthode** : POST  
- **URL** : `/api/get/2/221712152/t2`  
- **Corps JSON** :
```json
{
  "record": {
    "label": "New constant",
    "value": 3.14,
    "unit": "unit",
    "symbol": "π"
  }
}
```

---

### ✏️ PUT – Mettre à jour une cellule

- **Méthode** : PUT  
- **URL** : `/api/get/2/221712152/t2/l4/c3`  
- **Corps JSON** :
```json
{
  "value": "6.67E-11"
}
```

---

### 🗑️ DELETE – Supprimer une ligne

- **Méthode** : DELETE  
- **URL** : `/api/get/2/221712152/t2/l4`  

---

## 🛡️ Authentification API

Chaque requête nécessite :

- Un identifiant utilisateur numérique (`id_user`)
- Un `token` numérique généré à partir d'une clé alphanumérique
- Exemple : `id_user = 2` et `token = 221712152` (équivalent à `VAGABOB`)

---

## 🧠 Astuces

- Pour tester rapidement, utilisez [Postman](https://www.postman.com/) ou `curl`.
- En GET, ajoutez `?raw=true` pour ne récupérer que la valeur sans entête JSON.
- Toute la logique repose sur des fichiers `.php` isolés et autonomes par utilisateur.

