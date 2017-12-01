# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "bento/ubuntu-16.04"
  config.ssh.forward_agent = true

  config.vm.hostname = "pf3server"

  config.vm.network "private_network", ip: "192.168.31.10"

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "512"
    vb.name = "pf3server"
  end

  config.vm.synced_folder '.', '/vagrant', type: 'nfs'

  config.vm.provision "ansible" do |ansible|
    ansible.groups = {
      "pf3server" => [ "default" ],
    }

    ansible.playbook = "ansible/site.yml"

    ansible.extra_vars = { ansible_ssh_user: "vagrant", ansible_ssh_pipelining: "True" }
    ansible.sudo = true
  end
end
