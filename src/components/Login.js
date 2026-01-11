/**
 * Login. js - SMIPTV Web Player Login Component
 * 
 * Description: Beautiful modern login page with animated gradient backgrounds,
 *              glassmorphism effects, and smooth animations. 
 * 
 * Repository: irlam/webplayer
 * Created: 11/01/2026 (UK format:  DD/MM/YYYY)
 * Last Modified: 11/01/2026 - Fixed logo 404 error
 */

import React, {useState,useRef,useEffect} from 'react'
import styled, { keyframes } from 'styled-components';
import {useHistory} from "react-router-dom";
import {useAuth} from "../other/auth"
import Popup from "./Popup/Popup"
import Cookies from 'js-cookie'

// Animated gradient background
const gradientAnimation = keyframes`
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
`

// Float animation for the card
const floatAnimation = keyframes`
    0%, 100% {
        transform:  translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
`

// Glow pulse animation
const glowPulse = keyframes`
    0%, 100% {
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(139, 92, 246, 0.6);
    }
`

const Container = styled.div`
    position: absolute;
    width: 100%;
    height:  100%;
    top: 0;
    left: 0;
    background: linear-gradient(-45deg, #1a1a2e, #16213e, #0f3460, #533483);
    background-size: 400% 400%;
    animation: ${gradientAnimation} 15s ease infinite;
    transition: all . 5s ease;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;

    /* Animated background elements */
    &::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        top: -250px;
        right: -250px;
        animation: ${floatAnimation} 6s ease-in-out infinite;
    }

    &::after {
        content: '';
        position: absolute;
        width:  400px;
        height: 400px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        bottom: -200px;
        left: -200px;
        animation: ${floatAnimation} 8s ease-in-out infinite reverse;
    }
`

const Box = styled.form`
    position: relative;
    width: 90%;
    max-width: 450px;
    padding: 3rem 2.5rem;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius:  24px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    animation: ${floatAnimation} 6s ease-in-out infinite;
    z-index: 10;

    @media (max-width: 768px) {
        padding: 2rem 1.5rem;
        max-width: 95%;
    }

    & > h2 {
        color: white;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 700;
        text-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
    }

    & > h5 {
        color: rgba(255, 255, 255, 0.8);
        text-align: center;
        margin-bottom: 2rem;
        font-weight:  400;
        font-size: 1rem;
    }

    & > label {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        margin-bottom:  0.5rem;
        display: block;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }
`

const LogoContainer = styled.div`
    text-align: center;
    margin-bottom: 2rem;
    animation: ${glowPulse} 3s ease-in-out infinite;

    img {
        max-width: 70%;
        max-height: 120px;
        filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.6));
        transition: transform 0.3s ease;

        &:hover {
            transform: scale(1.05);
        }
    }

    @media (max-width:  768px) {
        img {
            max-width: 80%;
            max-height: 100px;
        }
    }
`

const Input = styled.input`
    margin-bottom: 1.5rem;
    background: rgba(255, 255, 255, 0.08) ! important;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white ! important;
    padding: 0.85rem 1.2rem;
    border-radius:  12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    width: 100%;

    &:: placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    &:focus {
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.3) !important;
        outline: none;
        transform: translateY(-2px);
    }

    &:hover {
        border-color: rgba(139, 92, 246, 0.5);
    }
`

const Button = styled.button`
    color: #fff;
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    border: none;
    padding:  1rem;
    border-radius: 12px;
    font-size:  1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 0.5rem;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    cursor: pointer;

    &:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
        transform: translateY(-2px);
        box-shadow:  0 6px 20px rgba(139, 92, 246, 0.6);
    }

    &:active {
        transform: translateY(0px);
        box-shadow: 0 2px 10px rgba(139, 92, 246, 0.4);
    }

    &:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.5);
    }
`

const InputGroup = styled.div`
    margin-bottom: 1.2rem;
`

const Login = ({url}) => {

    const [dns, setDns] = useState("");
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [showPopup, setShowPopup] = useState(false);
    const [blur, setBlur] = useState();
    const [m3u8, setM3u8] = useState(window.m3u8warning === true && ! Cookies.get("m3u8_play"));

    const history = useHistory();
    const auth = useAuth();

    useEffect(() => {
        if(auth.isAuth())
            history.push(url||"/")
        else auth.authLogin(()=>history.push(url||"/"))
    },[auth,history]);

    useEffect(()=>{
        m3u8 ?  setBlur({filter:"blur(. 3rem)"}) : setBlur({});
    },[m3u8])

    const inputRef = useRef(0)

    const remoteController = (event) => {
        let active = document.activeElement;
        if (event.keyCode === 40 && active. nextSibling) {
            if(active.nextSibling.tagName==="LABEL")
                active = active.nextSibling;
            active.nextSibling.focus();
        } else if (event.keyCode === 38 && active.previousSibling) {
            if(active. previousSibling.tagName==="LABEL")
                active = active.previousSibling;
            active.previousSibling. focus();
        } else if (event.keyCode === 13)
            active.click();
    }

    const login = (e) =>{
        e.preventDefault();
        setBlur({filter:"blur(.3rem)"})
        auth.signin(dns,username,password,
            () => window.location="/"
            ,
            (title,description) => {
                setShowPopup({title: title,description:description});
            }
        )
    }

    const closePopup = () =>{
        setBlur({filter:"blur(0)"});
        setShowPopup(false);
        inputRef.current.focus();
    }

    return (
        <>
        <Container style={blur}>
            <Box onKeyDown={remoteController} onSubmit={login}>
                <LogoContainer>
                    <img src="/img/banner_w.png" alt={window.playername} />
                </LogoContainer>

                <h5>Enter your credentials to continue</h5>

                {! window.dns && (
                    <InputGroup>
                        <label>Provider URL</label>
                        <Input 
                            ref={inputRef} 
                            className="form-control" 
                            type="text" 
                            spellCheck={false} 
                            placeholder="https://provider.com" 
                            name="dns" 
                            autoFocus 
                            onChange={(e)=> setDns(e.target. value)} 
                            value={dns} 
                        />
                    </InputGroup>
                )}

                <InputGroup>
                    <label>Username</label>
                    <Input 
                        ref={window.dns ? inputRef : null} 
                        className="form-control" 
                        type="text" 
                        spellCheck={false} 
                        placeholder="Enter your username" 
                        name="username" 
                        autoFocus={!! window.dns}
                        onChange={(e)=> setUsername(e.target.value)} 
                        value={username} 
                    />
                </InputGroup>

                <InputGroup>
                    <label>Password</label>
                    <Input 
                        className="form-control" 
                        type="password" 
                        spellCheck={false} 
                        placeholder="••••••••" 
                        name="password" 
                        onChange={(e)=> setPassword(e.target.value)} 
                        value={password}
                    />
                </InputGroup>

                <Button type="button" value="1" onClick={login} className="btn">
                    Sign In
                </Button>
            </Box>
        </Container>
        {showPopup && <Popup unsecure={true} title={showPopup.title} description={showPopup.description} icon={"fas fa-user-times"} onclick={closePopup}/>}
        {m3u8 && (
            <Popup unsecure={true} error={false} title={"Information"} description={"To login use your IPTV playlist username and password, not your email. <br/>Web Player can play live channels streams only in M3U8 format. <br/>The conversion will be done automatically if streams are in Xtreamcodes format (this won't affect your playlist)."} icon={"fas fa-info-circle"} onclick={()=> {Cookies.set("m3u8_play",1,{ expires: 365 }); setM3u8(! m3u8);}}/>
        )}
        </>
    )
}

export default Login
