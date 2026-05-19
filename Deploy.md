## Deploy

```
ssh root@2.24.103.97
aRcdC;#aS6YS@'1n
```

```
ssh ubuntu@2.24.103.97
6wL350C$qR1*
```

### actualizar y  usuario y configuracion

```
apt update && apt upgrade -y

adduser ubuntu
pass : 6wL350C$qR1*
usermod -aG sudo ubuntu

//  Agregá tu usuario al grupo docker:
sudo usermod -aG docker deploy

vi /etc/ssh/sshd_config  
# (PasswordAuthentication no, PermitRootLogin no)

systemctl restart ssh
```
### Configurá el firewall UFW:
```
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

### Usuario a github y copy/paste en github
- En GitHub, en tu repo → Settings → Deploy keys → Add deploy key
```
ssh-keygen -t ed25519 -f ~/.ssh/id_ubuntu -C "ubuntu@eljaguel"
mv ~/.ssh/id_ubuntu ~/.ssh/id_ed25519
mv ~/.ssh/id_ubuntu.pub ~/.ssh/id_ed25519.pub
ssh -T git@github.com // comprueba la coneccion
cat ~/.ssh/id_ed25519.pub | pbcopy
```
#### Entrar con ubuntu y clonar proyecto
```
mkdir -p ~/apps && cd ~/apps
git clone git@github.com:danieltho/jaguel.git

```
#### Copiar .env.example a .env
```
cp .env.example .env
```
#### Iniciar por primera vez
```
docker compose up -d --build
docker compose ps
docker compose logs -f app
```