import {Link} from 'react-router-dom';
import {createRef, useState} from 'react';
import axiosClient from '../axios-client.js';
import {useStateContext} from '../context/ContextProvider.jsx';
import Button from '../components/Button';
import Input from '../components/Input';
import H1 from '../components/H1';

export default function Signup() {
  const nameRef = createRef()
  const emailRef = createRef()
  const passwordRef = createRef()
  const passwordConfirmationRef = createRef()
  const {setUser, setToken} = useStateContext()
  const [errors, setErrors] = useState(null)

  const initialState = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  };
  const [state, setState] = useState(initialState);

  const handleOnchange = (name, value) => {
    let newState = {
      ...state,
      [name]: value
    }
    setState(newState);
  }

  const onSubmit = ev => {
    ev.preventDefault()

    axiosClient.post('/signup', state)
      .then(({data}) => {
        setUser(data.user)
        setToken(data.token);
      })
      .catch(err => {
        const response = err.response;
        if (response && response.status === 422) {
          setErrors(response.data.errors)
        }
      })
  }

  return (
    <div className="h-[100vh] flex justify-center items-center">
      <div className="w-[360px] relative z-1 bg-white/40 p-6 shadow">
        <form onSubmit={onSubmit}>
          <H1>Signup for document verification</H1>
          <Input value={state.name} type="text" placeholder="Full name" onChange={event => handleOnchange('name', event.currentTarget.value)} />
          <Input value={state.email} type="email" placeholder="Email" onChange={event => handleOnchange('email', event.currentTarget.value)} />
          <Input value={state.password} type="password" placeholder="Password" onChange={event => handleOnchange('password', event.currentTarget.value)} />
          <Input value={state.password_confirmation} type="password" placeholder="Repeat password" onChange={event => handleOnchange('password_confirmation', event.currentTarget.value)} />
          {errors &&
              <div className="text-rose-600 my-5">
                {Object.keys(errors).map(key => errors[key].map((err, ind) => <p key={key + ind}>{err}</p>))}
              </div>
          }
          <Button>Signup</Button>
          <p className="text-slate-400 mt-4">Already registered? <Link to="/signin" className="text-slate-700">Sign In</Link></p>
        </form>
      </div>
    </div>
  )
}
